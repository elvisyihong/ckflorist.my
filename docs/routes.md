# Route map

## Public HTML

| Method | Route | Purpose |
| --- | --- | --- |
| GET | `/` | Editorial home and featured florist/café content |
| GET | `/florist` | Filterable inspiration catalogue |
| GET | `/florist/{slug}` | Sample detail, gallery, tags, and price estimate |
| GET | `/customise` | Twelve-step bouquet builder |
| GET | `/cafe` | Café menu by category |
| GET | `/gallery` | Paginated gallery |
| GET | `/about` | Story, values, and disclaimers |
| GET | `/contact` | Contact, hours, map, and policies |
| GET | `/selection` | Florist and café selection summary |
| GET | `/enquiry/{reference}` | Enquiry receipt and WhatsApp continuation |
| GET | `/policies/{slug}` | Terms, privacy, cancellation, delivery, or pickup policy |

## Public actions and JSON

| Method | Route | Purpose |
| --- | --- | --- |
| GET | `/api/florist/matches` | Ranked active samples for flowers and optional preferences |
| POST | `/api/selection/bouquet` | Validate and persist bouquet draft in session |
| POST | `/api/selection/cafe` | Add/update a café item in session |
| DELETE | `/api/selection/cafe/{key}` | Remove one café selection |
| GET | `/api/selection` | Current selection and count |
| POST | `/enquiries` | Validate, save enquiry, and return WhatsApp URL |

All JSON mutations require `X-CSRF-Token`. Responses use `{ok,data,error,errors}` and never expose stack traces.

## Admin

| Method | Route | Purpose |
| --- | --- | --- |
| GET/POST | `/admin/login` | Auth form and rate-limited login |
| POST | `/admin/logout` | CSRF-protected logout |
| GET | `/admin` | Operational dashboard |
| GET | `/admin/settings` | Shop settings |
| POST | `/admin/settings` | Validate and save settings |
| GET | `/admin/{resource}` | Paginated allowlisted resource index |
| GET | `/admin/{resource}/create` | Resource create form |
| POST | `/admin/{resource}` | Create resource |
| GET | `/admin/{resource}/{id}/edit` | Resource edit form |
| POST | `/admin/{resource}/{id}` | Update resource |
| POST | `/admin/{resource}/{id}/delete` | Delete/deactivate resource |
| GET | `/admin/enquiries/{id}` | Enquiry timeline and detail |
| POST | `/admin/enquiries/{id}/status` | Permission-checked status transition |
| POST | `/admin/uploads` | Validated image upload and derivative creation |

Admin resources are declared in `config/admin_resources.php`; URL values can never select arbitrary tables or columns.

