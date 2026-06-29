# Backend Test

Laravel 10 API slice for ticket tier CRUD on an events platform.

## Assumptions

- Permission names are:
  - `ticket-tiers.view-any`
  - `ticket-tiers.view`
  - `ticket-tiers.create`
  - `ticket-tiers.update`
  - `ticket-tiers.delete`
  - `ticket-tiers.publish`
- `sales_channels = null` means the tier is available on every channel.
- Allowed sales channels are `web`, `mobile`, `box_office`, and `partner`.
- A minimal `events` table is included only so `ticket_tiers.event_id` can reference an existing event.
- API routes are protected with Sanctum's `auth:sanctum` middleware.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Tests

The test suite uses SQLite in memory via `phpunit.xml`.

```bash
php artisan test
```

## API

Routes are registered under `/api`:

- `GET /ticket-tiers`
- `POST /ticket-tiers`
- `GET /ticket-tiers/{ticket_tier}`
- `PUT/PATCH /ticket-tiers/{ticket_tier}`
- `DELETE /ticket-tiers/{ticket_tier}`
- `POST /ticket-tiers/{ticket_tier}/publish`

Index supports:

- `filter[event_id]=1`
- `filter[channel]=web`
- `sort=name`, `sort=-price`, `sort=created_at`
- `include=event`
- `per_page=15`

## Postman

Import `postman/ticket-tiers.postman_collection.json`.
