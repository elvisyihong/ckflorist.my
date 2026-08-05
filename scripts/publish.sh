#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(git -C "$SCRIPT_DIR/.." rev-parse --show-toplevel)"

REMOTE="${GIT_REMOTE:-origin}"
BRANCH="${GIT_BRANCH:-$(git -C "$REPO_DIR" branch --show-current)}"
DEPLOY_HOST="${DEPLOY_HOST:-47.250.56.122}"
DEPLOY_PATH="${DEPLOY_PATH:-/opt/1panel/www/sites/ckflorist.my/index}"
DEPLOY_REMOTE="${DEPLOY_REMOTE:-origin}"
DEPLOY_PROXY_CONTAINER="${DEPLOY_PROXY_CONTAINER:-1Panel-openresty-rznE}"
DEPLOY_PHP_CONTAINER="${DEPLOY_PHP_CONTAINER:-PHP8}"
CREDENTIALS_FILE="${DEPLOY_ENV_FILE:-$SCRIPT_DIR/.env}"
MODE="publish"

if [[ "${1:-}" == "--check" ]]; then
  MODE="check"
  shift
elif [[ "${1:-}" == "--help" || "${1:-}" == "-h" ]]; then
  printf 'Usage: %s [--check] [commit message]\n' "$0"
  printf '  --check  Validate local and server deployment settings without changing anything.\n'
  exit 0
fi

COMMIT_MESSAGE="${*:-Update $(date '+%Y-%m-%d %H:%M:%S')}"

if [[ -z "$BRANCH" ]]; then
  printf 'Error: cannot publish from a detached HEAD.\n' >&2
  exit 1
fi

if [[ -z "$DEPLOY_PROXY_CONTAINER" || -z "$DEPLOY_PHP_CONTAINER" ]]; then
  printf 'Error: deployment container names cannot be empty.\n' >&2
  exit 1
fi

if [[ ! -f "$CREDENTIALS_FILE" ]]; then
  printf 'Error: credentials file not found: %s\n' "$CREDENTIALS_FILE" >&2
  exit 1
fi

if ! git -C "$REPO_DIR" remote get-url "$REMOTE" >/dev/null 2>&1; then
  printf 'Error: Git remote does not exist: %s\n' "$REMOTE" >&2
  exit 1
fi

if git -C "$REPO_DIR" ls-files --error-unmatch "$CREDENTIALS_FILE" >/dev/null 2>&1; then
  printf 'Error: credentials file is tracked by Git: %s\n' "$CREDENTIALS_FILE" >&2
  exit 1
fi

if [[ "$CREDENTIALS_FILE" == "$REPO_DIR"/* ]] &&
   ! git -C "$REPO_DIR" check-ignore -q "$CREDENTIALS_FILE"; then
  printf 'Error: credentials file is inside the repository but is not ignored: %s\n' \
    "$CREDENTIALS_FILE" >&2
  exit 1
fi

if ! command -v expect >/dev/null 2>&1; then
  printf 'Error: expect is required for password-based SSH deployment.\n' >&2
  exit 1
fi

if [[ "$MODE" == "check" ]]; then
  printf 'Local configuration is valid. Checking %s:%s...\n' "$DEPLOY_HOST" "$DEPLOY_PATH"
else
  printf 'Staging all local changes...\n'
  git -C "$REPO_DIR" add -A

  if git -C "$REPO_DIR" diff --cached --quiet; then
    printf 'No new changes to commit.\n'
  else
    printf 'Creating commit: %s\n' "$COMMIT_MESSAGE"
    git -C "$REPO_DIR" commit -m "$COMMIT_MESSAGE"
  fi

  printf 'Pushing %s to GitHub remote %s...\n' "$BRANCH" "$REMOTE"
  git -C "$REPO_DIR" push -u "$REMOTE" "$BRANCH"
fi

if [[ "$MODE" == "publish" ]]; then
  printf 'Pulling %s and restarting %s and %s on %s...\n' \
    "$BRANCH" "$DEPLOY_PROXY_CONTAINER" "$DEPLOY_PHP_CONTAINER" "$DEPLOY_HOST"
fi
expect -f - \
  "$CREDENTIALS_FILE" \
  "$DEPLOY_HOST" \
  "$DEPLOY_PATH" \
  "$BRANCH" \
  "$DEPLOY_REMOTE" \
  "$DEPLOY_PROXY_CONTAINER" \
  "$DEPLOY_PHP_CONTAINER" \
  "$MODE" <<'EXPECT_SCRIPT'
set timeout 60

set credentials_file [lindex $argv 0]
set deploy_host [lindex $argv 1]
set deploy_path [lindex $argv 2]
set deploy_branch [lindex $argv 3]
set deploy_remote [lindex $argv 4]
set deploy_proxy_container [lindex $argv 5]
set deploy_php_container [lindex $argv 6]
set mode [lindex $argv 7]

set username ""
set password ""
set file_handle [open $credentials_file r]

while {[gets $file_handle line] >= 0} {
    if {[regexp {^[[:space:]]*(username|DEPLOY_USER)[[:space:]]*[:=][[:space:]]*(.*?)[[:space:]]*$} $line -> key value]} {
        set username $value
    }
    if {[regexp {^[[:space:]]*(password|DEPLOY_PASSWORD)[[:space:]]*[:=][[:space:]]*(.*?)[[:space:]]*$} $line -> key value]} {
        set password $value
    }
}

close $file_handle

if {$username eq "" || $password eq ""} {
    puts stderr "Error: credentials file must define username and password."
    exit 1
}

proc shell_quote {value} {
    set escaped [string map [list "'" "'\"'\"'"] $value]
    return "'$escaped'"
}

if {$mode eq "check"} {
    set deploy_script {
        test -d "$1" &&
        test "$(git -C "$1" rev-parse --is-inside-work-tree)" = "true" &&
        git -C "$1" remote get-url "$3" >/dev/null &&
        test -z "$(git -C "$1" status --porcelain)" &&
        docker inspect "$4" "$5" >/dev/null
    }
} else {
    set deploy_script {
        test -z "$(git -C "$1" status --porcelain)" &&
        git -C "$1" pull --ff-only "$3" "$2" &&
        docker restart "$4" "$5"
    }
}
set remote_command "sh -lc [shell_quote $deploy_script] sh \
    [shell_quote $deploy_path] \
    [shell_quote $deploy_branch] \
    [shell_quote $deploy_remote] \
    [shell_quote $deploy_proxy_container] \
    [shell_quote $deploy_php_container]"
spawn ssh \
    -o ConnectTimeout=10 \
    -o ServerAliveInterval=15 \
    -o ServerAliveCountMax=3 \
    "$username@$deploy_host" \
    $remote_command

expect {
    -re {(?i)are you sure you want to continue connecting} {
        send -- "yes\r"
        exp_continue
    }
    -re {(?i)password:} {
        send -- "$password\r"
        exp_continue
    }
    timeout {
        puts stderr "Error: SSH deployment timed out."
        exit 1
    }
    eof
}

set result [wait]
exit [lindex $result 3]
EXPECT_SCRIPT

if [[ "$MODE" == "check" ]]; then
  printf 'Publish check completed successfully; no changes were made.\n'
else
  printf 'Publish and deployment completed successfully.\n'
fi
