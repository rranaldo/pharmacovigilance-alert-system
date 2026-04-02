## Stack

- **API**: PHP 8.2 / Laravel 12 / Sanctum for token auth
- **Frontend**: Vue 3 (Composition API) / Pinia / Vue Router / Tailwind CSS 4
- **Database**: MySQL 8
- **Dev tooling**: Docker Compose, Vite, Mailpit

## Quick start

### Docker (recommended)

```bash
cp backend/.env.example backend/.env
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
```

The frontend container runs `npm install && npm run dev` automatically. Once up, the services are at:

- Frontend: http://localhost:5173
- API: http://localhost:8000/api
- Mailpit: http://localhost:8025

### Local (without Docker)

```bash
# backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
# edit .env with your MySQL credentials
php artisan migrate:fresh --seed
php artisan serve --port=8000

# frontend (separate terminal)
cd frontend
npm install
npm run dev
```

## Test credentials

- `admin` / `password` — Admin
- `jsmith` / `password` — Pharmacist
- `mgarcia` / `password` — Pharmacist

Use lot number `951357` to see seeded data with affected orders.

## Project structure

The backend follows standard Laravel layout. Controllers in `app/Http/Controllers/Api/` are kept thin — they validate input via Form Requests and hand off to services. Business logic lives in `app/Services/` (AlertService, AuditService, ExportService). Models in `app/Models/` carry query scopes for the most common lookups.

The frontend lives under `frontend/src/`. Views are the page-level components, components holds reusable UI pieces, stores has the Pinia slices for auth, orders, and alerts, and `services/api.js` is the single Axios setup. The `vite.config.js` proxies `/api` to Laravel to avoid CORS issues in dev.

## API endpoints

All routes are prefixed with `/api`. Protected routes require `Authorization: Bearer <token>`.

### Auth

- `POST /api/login` — returns a Sanctum token
- `POST /api/logout` — revokes the current token
- `GET /api/user` — current user info

### Medications

- `GET /api/medications/search` — search by `lot`, `start_date`, `end_date`

### Orders

- `GET /api/orders` — paginated list, filterable by lot and date range
- `GET /api/orders/{id}` — full detail with line items and alerts
- `GET /api/orders/export` — CSV download of the current filter

### Alerts

- `GET /api/alerts` — paginated alert history
- `POST /api/alerts/send` — send an alert to one customer; body: `customer_id`, `order_id`, `lot_number`, optional `message`
- `POST /api/alerts/send-bulk` — send to multiple customers; body: `customer_ids[]`, `lot_number`, optional `message`

### Customers

- `GET /api/customers/{id}` — profile with associated orders and alerts

## Database

Seven tables. `users` holds system accounts with a role (admin or pharmacist). `customers` are the patients who receive medication. `medications` stores name and lot number. `orders` ties a customer to a purchase date. `order_items` is the line detail linking an order to a medication. `alerts` records every notification sent — who received it, who sent it, the status, and which lot triggered it. `audit_logs` tracks actions with entity type, entity id, a JSON details blob, and the IP address.

## Design notes

The service layer keeps all business logic out of controllers. The query scopes `Order::withLotNumber()` and `Order::inDateRange()` encapsulate the core queries; date range defaults to the last 30 days when omitted.

Token auth (Sanctum) is used instead of session cookies because the frontend is a standalone SPA and tokens avoid CSRF complications across origins.

`AlertService` checks for an existing sent alert before dispatching, so the same customer won't get notified twice for the same order and lot.

The CSV export uses `cursor()` inside a streamed response to avoid loading large result sets into memory.

Audit records store `entity_type` and `entity_id` as plain columns rather than a polymorphic relationship, so audit history survives even if the referenced entity gets deleted.

## Running tests

```bash
cd backend
php artisan test
```

Tests run against SQLite in-memory. Coverage includes auth, medication search, orders (listing, detail, export), and alerts (single, bulk, history).

## Known limitations

- SMS alerts are modeled in the DB but not implemented as a sender — email only for now.
- Role middleware is registered but not applied to routes yet. All authenticated users can access all endpoints.
- The CSV export endpoint doesn't carry the Bearer token when opened via `window.open`. In production this would need a signed URL or a cookie-based fallback.
- Lot numbers are not unique per medication — different meds can share a lot from the same manufacturing batch.
