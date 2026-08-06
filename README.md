# CK Florist

Production-oriented PHP 8 and MySQL enquiry website for a combined florist and café. Customers browse inspiration, build a bouquet, add café items, save an enquiry, receive a `CKF-YYYYMMDD-XXXXX` reference, and continue through WhatsApp. There is no payment or customer account flow.

## Requirements

- PHP 8.2+ with PDO MySQL, GD, Fileinfo, JSON, Mbstring, and Session
- MySQL 8+ or current MariaDB
- OpenResty/Nginx or Apache with front-controller rewriting
- Writable `storage/cache`, `storage/logs`, and `public/uploads`

## Local setup

1. Copy `.env.example` to `.env` and set a random `APP_KEY`, database credentials, and WhatsApp number.
2. Create the database and run `database/migrations/001_initial.sql` followed by `database/seeds/001_catalogue.sql`.
3. Create the first owner: `php database/create_admin.php owner@example.com "Owner Name"`.
4. Start locally: `SESSION_SECURE=false APP_ENV=development php -S 127.0.0.1:8787 router.php`.
5. Open `http://127.0.0.1:8787`.

Do not commit `.env`; it is ignored. The deployment credentials in `scripts/.env` are also ignored.

## Tests

```bash
SESSION_SECURE=false APP_ENV=testing php tests/run.php
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
node --check public/assets/js/app.js
```

The test suite covers all flower matching rules, tie breakers, enquiry references, server validation, WhatsApp message structure, and schema normalization. HTTP smoke tests should cover every public route, session selection, AJAX matching, café addition, and CSRF rejection.

## Production deployment

1. Point the site root to the repository root.
2. Add the locations from `deploy/nginx-location.conf` to the site server block.
3. Set `.env` permissions to `0600`; set writable directories to the PHP-FPM user.
4. Run migrations explicitly before switching traffic.
5. Run `./scripts/publish.sh --check`, then `./scripts/publish.sh "Deployment message"`.

The publish script targets `/opt/1panel/www/sites/ckflorist.my/index`, refuses dirty server deployments, pulls with `--ff-only`, and restarts OpenResty and PHP8.

## Phase record

- Architecture and schema: `docs/architecture.md` and `database/migrations/001_initial.sql`
- Routes: `docs/routes.md` and `config/routes.php`
- Design system: `docs/design-system.md` and `public/assets/css/app.css`
- Responsive wireframes: `docs/wireframes.md`
- Admin foundation: `app/Controllers/Admin`, role permissions, rate limiting, media validation, audit logs
- Florist and matching: normalized sample relations, `FlowerMatcher`, unit tests
- Bouquet builder: twelve accessible steps with session persistence and server validation
- Café: categories, products, options, add-ons, dietary labels, temporary selection
- Enquiries and WhatsApp: transactional persistence, unique references, structured URL-encoded message
- Hardening: output escaping, CSRF, CSP/security headers, secure sessions, responsive images, settings cache, pagination, reduced-motion and focus support

Generated project imagery was created with the built-in image generation workflow and saved as optimized responsive WebP assets under `public/assets/images`.

