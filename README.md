# selfOrderingBackend

Laravel API backend for the family self-ordering mini program.

## Stack

- PHP 8.3
- Laravel 12
- Nginx
- PHP-FPM
- MySQL
- Redis

## Local Setup

Copy the environment file:

```bash
cp .env.example .env
```

Build the PHP-FPM image:

```bash
podman build --network host -t localhost/self-ordering-api:dev -f docker/php/Dockerfile .
```

Install PHP dependencies through the Composer tool container:

```bash
podman run --rm --network host -v "$PWD:/app:Z" -w /app composer:2 composer install --no-security-blocking
```

Start the Podman pod:

```bash
scripts/podman-up.sh
```

Run database migrations after MySQL is ready:

```bash
podman exec self-ordering-php-fpm php artisan migrate
```

Default ports:

- API: `http://localhost:8080`
- HTTPS placeholder: `https://localhost:8443`
- MySQL: `127.0.0.1:3307`
- Redis: `127.0.0.1:6379`

Pod layout:

- `myapp-pod`
- `self-ordering-nginx`
- `self-ordering-php-fpm`
- `self-ordering-mysql`
- `self-ordering-redis`
- `self-ordering-queue`
- `self-ordering-scheduler`

More details are documented in `../docs/architecture/podman-dev-environment.md`.

## API Contract

The API route contract is defined in `routes/api.php`, documented in
`../docs/api-contract.md`, and covered by Feature tests. The implemented MVP
includes:

- WeChat development login and role selection.
- Chef profile, dish CRUD, dish image upload, diner bindings, and order actions.
- Diner chef binding, bound chef menu, order creation, order listing, detail,
  and cancellation.

Run the backend test suite:

```bash
podman run --rm -v "$PWD:/app:Z" -w /app localhost/self-ordering-api:dev vendor/bin/phpunit
```

The JSON response shape follows the common `{ code, message, data }` wrapper and
uses `camelCase` fields for frontend-facing payloads.
