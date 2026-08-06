#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="${APP_DIR:-/opt/1panel/www/sites/ckflorist.my/index}"
SITE_CONFIG="${SITE_CONFIG:-/opt/1panel/www/conf.d/ckflorist.my.conf}"
DATABASE_NAME="${DATABASE_NAME:-ckflorist_db}"
DATABASE_USER="${DATABASE_USER:-ckflorist_app}"
PHP_CONTAINER="${DEPLOY_PHP_CONTAINER:-PHP8}"
PROXY_CONTAINER="${DEPLOY_PROXY_CONTAINER:-1Panel-openresty-rznE}"

if [[ "${EUID}" -ne 0 ]]; then
  printf 'Error: run this script as root on the production server.\n' >&2
  exit 1
fi

for required in "$APP_DIR/index.php" "$APP_DIR/database/migrations/001_initial.sql" "$APP_DIR/database/seeds/001_catalogue.sql" "$SITE_CONFIG"; do
  if [[ ! -f "$required" ]]; then
    printf 'Error: required file is missing: %s\n' "$required" >&2
    exit 1
  fi
done

database_container="$(docker ps --format '{{.Names}} {{.Image}}' | awk 'tolower($0) ~ /mariadb|mysql/ {print $1; exit}')"
if [[ -z "$database_container" ]]; then
  printf 'Error: no running MySQL or MariaDB container was found.\n' >&2
  exit 1
fi

table_count="$(docker exec "$database_container" sh -lc \
  'root_password="${MARIADB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"; test -n "$root_password"; mariadb -N -uroot -p"$root_password" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '\''$1'\'';"' \
  sh "$DATABASE_NAME" 2>/dev/null)"

if [[ "$table_count" == "0" ]]; then
  printf 'Applying database schema and catalogue seed...\n'
  docker exec "$database_container" sh -lc \
    'root_password="${MARIADB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"; mariadb -uroot -p"$root_password" -e "CREATE DATABASE IF NOT EXISTS \`$1\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"' \
    sh "$DATABASE_NAME"
  docker exec -i "$database_container" sh -lc \
    'root_password="${MARIADB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"; mariadb -uroot -p"$root_password" "$1"' \
    sh "$DATABASE_NAME" < "$APP_DIR/database/migrations/001_initial.sql"
  docker exec -i "$database_container" sh -lc \
    'root_password="${MARIADB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"; mariadb -uroot -p"$root_password" "$1"' \
    sh "$DATABASE_NAME" < "$APP_DIR/database/seeds/001_catalogue.sql"
else
  printf 'Database already contains %s tables; migrations were not replayed.\n' "$table_count"
fi

if [[ ! -f "$APP_DIR/.env" ]]; then
  printf 'Creating least-privilege database credentials and application environment...\n'
  application_password="$(openssl rand -hex 24)"
  application_key="$(openssl rand -hex 32)"
  docker exec "$database_container" sh -lc \
    'root_password="${MARIADB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"; app_password="$2"; mariadb -uroot -p"$root_password" -e "CREATE USER IF NOT EXISTS '\''$1'\''@'\''%'\'' IDENTIFIED BY '\''$app_password'\''; ALTER USER '\''$1'\''@'\''%'\'' IDENTIFIED BY '\''$app_password'\''; GRANT SELECT, INSERT, UPDATE, DELETE ON \`$3\`.* TO '\''$1'\''@'\''%'\''; FLUSH PRIVILEGES;"' \
    sh "$DATABASE_USER" "$application_password" "$DATABASE_NAME"

  umask 027
  printf '%s\n' \
    'APP_ENV=production' \
    'APP_DEBUG=false' \
    'APP_URL=https://ckflorist.my' \
    "APP_KEY=$application_key" \
    'APP_TIMEZONE=Asia/Brunei' \
    'SESSION_SECURE=true' \
    "DB_HOST=$database_container" \
    'DB_PORT=3306' \
    "DB_DATABASE=$DATABASE_NAME" \
    "DB_USERNAME=$DATABASE_USER" \
    "DB_PASSWORD=$application_password" \
    'WHATSAPP_NUMBER=6730000000' \
    'UPLOAD_MAX_BYTES=5242880' > "$APP_DIR/.env"
  chown root:1000 "$APP_DIR/.env"
  chmod 0640 "$APP_DIR/.env"
else
  printf 'Existing application environment preserved.\n'
fi

mkdir -p "$APP_DIR/public/uploads" "$APP_DIR/storage/cache" "$APP_DIR/storage/logs" "$APP_DIR/storage/uploads"
chown -R 1000:1000 "$APP_DIR/public/uploads" "$APP_DIR/storage"
find "$APP_DIR/public/uploads" "$APP_DIR/storage" -type d -exec chmod 0750 {} +
find "$APP_DIR/public/uploads" "$APP_DIR/storage" -type f -exec chmod 0640 {} +

if ! grep -Fq 'try_files $uri $uri/ /index.php?$query_string;' "$SITE_CONFIG"; then
  printf 'Backing up and adding front-controller routes to OpenResty...\n'
  backup="$SITE_CONFIG.backup-$(date '+%Y%m%d%H%M%S')"
  cp -p "$SITE_CONFIG" "$backup"
  temporary="$(mktemp)"
  awk '
    !inserted && /location ~ \[\^\/\]\\\.php/ {
      print "    location / {"
      print "        try_files $uri $uri/ /index.php?$query_string;"
      print "    }"
      print "    location ~* \\.(css|js|jpg|jpeg|png|webp|avif|svg|woff2)$ {"
      print "        expires 30d;"
      print "        add_header Cache-Control \"public, immutable\";"
      print "        try_files $uri =404;"
      print "    }"
      print "    location ~ ^/(app|config|database|docs|storage|tests|deploy|scripts)/ {"
      print "        return 404;"
      print "    }"
      inserted=1
    }
    { print }
    END { if (!inserted) exit 42 }
  ' "$SITE_CONFIG" > "$temporary" || {
    cp -p "$backup" "$SITE_CONFIG"
    printf 'Error: could not locate the PHP location; configuration restored.\n' >&2
    exit 1
  }
  cp "$temporary" "$SITE_CONFIG"
  rm -f "$temporary"
  if ! docker exec "$PROXY_CONTAINER" nginx -t; then
    cp -p "$backup" "$SITE_CONFIG"
    printf 'Error: OpenResty validation failed; configuration restored from %s.\n' "$backup" >&2
    exit 1
  fi
else
  printf 'OpenResty front-controller routes are already configured.\n'
fi

printf 'Verifying PHP database connectivity...\n'
docker exec "$PHP_CONTAINER" php -r \
  'require "/www/sites/ckflorist.my/index/bootstrap.php"; if (!App\Core\Database::available()) { fwrite(STDERR, "Database connection failed.\n"); exit(1); } echo "Database connection is valid.\n";'

docker restart "$PROXY_CONTAINER" "$PHP_CONTAINER" >/dev/null
printf 'Production configuration completed successfully.\n'
