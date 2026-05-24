# AlBaik Store Setup

## Local Docker setup

This project requires PHP 8.2+ with MySQL, DOM/XML, Intl, Zip, and related extensions. If those extensions are not installed on the host, use the included Docker setup:

```bash
docker compose build app
docker compose up -d mysql
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --seed
npm ci --legacy-peer-deps
npm run build
docker compose up -d app
```

Open:

```text
http://localhost:8000
http://localhost:8000/admin
```

MySQL is exposed on host port `3307` and is available to Laravel inside Docker as:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=albaik_store
DB_USERNAME=albaik
DB_PASSWORD=albaik_password
```

Verify the setup:

```bash
docker compose run --rm app composer check-platform-reqs
docker compose run --rm app php artisan test
```

If you want Vite dev mode instead of a static build:

```bash
docker compose --profile frontend up -d node
```

Then keep the app running at `http://localhost:8000`.

## Manual host setup

1. Copy environment and set database credentials:

```bash
cp .env.example .env
# edit .env (DB_* and APP_URL)
```

2. Install PHP deps:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
```

3. Install JS deps and build:

```bash
npm install --legacy-peer-deps
npm run dev
```

## Production checklist

Use MySQL and Redis in production:

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

Then run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:work redis --tries=3 --timeout=90
```

Recommended services:

- Supervisor or systemd for `queue:work`.
- Cron entry for Laravel scheduler: `php artisan schedule:run` every minute.
- HTTPS, secure cookies, and a production mail driver.
- Redis-backed cache, sessions, and queues.
- Daily database backups and object storage for uploaded media.

Notes:

- React entry: `resources/js/entry.jsx`
- Filament admin installed via Composer
- See `vite.config.js` for built assets
- Admin panel: `/admin`
- API routes are intentionally disabled in the current Filament-first phase.

## Demo accounts

After running `php artisan db:seed`, use:

- Admin: `admin@qr.com` / `password`
- Super Admin: `admin@albaikstore.local` / `password`
- Customer: `customer@qr.com` / `password`
- Wholesale Customer: `wholesale@qr.com` / `password`

## Current admin modules

- Products, variants, images, categories, brands, tags, suppliers
- Orders, payments, payment methods, invoices, order timeline
- Shipping methods, shipping zones, shipping rules
- Coupons and flash sales
- Warehouses and inventory movements
- Reviews, banners, settings, currencies
- Users, roles, permissions
- Activity logs
- Dashboard stats and latest orders
