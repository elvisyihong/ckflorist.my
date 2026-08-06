# CK Florist architecture

## Product boundary

CK Florist is a server-rendered enquiry experience, not a payment checkout. Guests can combine florist inspiration and café items, persist an unfinished selection in their session, submit validated details, receive a `CKF-YYYYMMDD-XXXXX` reference, and continue in WhatsApp. Opening WhatsApp records intent only; it never confirms an order.

## Runtime shape

```text
Browser
  -> OpenResty / PHP 8
  -> public front controller
  -> Router -> Controller -> Repository / Service
  -> PDO prepared statements -> MySQL 8+
  -> server-rendered views + small progressive-enhancement JavaScript
```

The application uses no framework and no Composer runtime dependency. `bootstrap.php` registers a PSR-4-style autoloader, loads environment values, configures secure sessions, and creates the dependency container. `index.php` is the single web entry point. `router.php` supports PHP's local development server.

## Layers

- `app/Core`: routing, request/response helpers, PDO connection, views, CSRF, sessions, authentication, validation, and rate limiting.
- `app/Controllers`: public and admin request coordination. Controllers do not build SQL.
- `app/Repositories`: prepared-query access for catalogue, builder data, enquiries, and admin resources.
- `app/Services`: flower ranking, enquiry references, WhatsApp messages, uploads, cached settings, and audit records.
- `config`: routes, application defaults, admin resource allowlists, roles, and permissions.
- `views`: escaped PHP templates and reusable UI components.
- `database`: normalized MySQL schema and safe seed data.
- `public/assets`: versioned CSS, JavaScript, and generated local imagery.
- `storage`: non-public logs, cache, and uploads. Production should serve only generated public derivatives, never raw uploads.

## Request and trust boundaries

1. The router accepts only declared HTTP method/path pairs.
2. Every state-changing request validates a session-bound CSRF token.
3. Controllers normalize and validate input server-side.
4. Repositories use prepared statements; identifiers come only from server-side allowlists.
5. Views escape output by default with `e()`; explicitly trusted policy HTML is sanitized before storage.
6. Admin routes require authentication and a named permission.
7. Uploads are decoded and re-encoded after MIME inspection, assigned random names, and never retain user filenames.
8. Admin authentication is rate-limited per normalized username and IP and uses session ID rotation.
9. Important admin mutations create append-only audit records.
10. Production exceptions receive a request ID, are logged outside the web root, and return a generic error page.

## Domain model

Florist samples and flowers use `florist_sample_flowers`, which records `is_main`, dominance weight, and sort order. Colour, occasion, wrapping, and decoration tags are separate many-to-many relations. Café product variants use normalized size/temperature options and add-ons. Enquiries snapshot selected labels and prices into JSON while retaining optional foreign keys for reporting.

## Selection lifecycle

```text
session draft -> server validation -> database transaction
              -> enquiry + line snapshots + event(whatsapp_opened)
              -> signed success payload -> URL-encoded wa.me link
```

Session data expires after seven days. Enquiry creation re-queries active catalogue records so browser-supplied names or prices are never trusted.

## Deployment

The existing `scripts/publish.sh` pushes the current branch, pulls it into `/opt/1panel/www/sites/ckflorist.my/index`, and restarts OpenResty and PHP8. Production configuration is documented in `deploy/nginx.conf.example` and `.env.example`. Database migrations are deliberately explicit and are not executed automatically during a web request.

