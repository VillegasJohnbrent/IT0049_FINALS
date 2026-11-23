# POS System

Laravel 12, Blade, PHP 8.2+ (64-bit), and SQLite or MySQL. No Node build is needed.

## Local setup

Install PHP and Composer 2. Enable the PHP extensions required by Laravel, plus Fileinfo and PDO SQLite. Run these commands inside this folder:

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://localhost:8000.

Local login: `staff@example.com` / `ChangeMe123!`. Change this password under Staff. The local seeder adds three products with sample images and one customer. Re-running it does not reset existing stock or passwords.

On Windows, enable Developer Mode or use an administrator terminal if `storage:link` fails.

## MySQL

Create a `pos_system` database and replace the database settings in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_system
DB_USERNAME=root
DB_PASSWORD=
```

Enable PDO MySQL, run `php artisan config:clear`, then `php artisan migrate --seed`.

## Use

- All staff accounts can manage products, customers, and staff.
- Products can be archived and restored.
- Customer and staff deletion removes them from active lists. Past sales stay linked.
- The signed-in account cannot delete itself.
- Record Sale accepts one product and an optional customer.
- A transaction saves the sale and deducts stock together. Insufficient stock leaves both unchanged.
- Sales retain the names and price used at purchase. Re-submitting the same sale form does not deduct stock twice.
- Uploads accept JPG, PNG, and WebP, up to 2 MB and 4096 × 4096 pixels.
- Prices use Philippine pesos. Dates use `APP_TIMEZONE`.

## Tests

```sh
composer test
```

Tests use a separate in-memory SQLite database. They cover access, CSRF, validation, uploads, account management, sale totals, rollback, duplicate submissions, stock protection, and history.

## Deployment

1. Use PHP hosting with a persistent database and persistent `storage` folder. Point the web document root to `public`.
2. Run `composer install --no-dev --optimize-autoloader`.
3. Create `.env`. Set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`, and database settings. Set a private `SEED_STAFF_EMAIL` and `SEED_STAFF_PASSWORD` before seeding.
4. Generate `APP_KEY` once with `php artisan key:generate`. Keep this key on later releases.
5. Run `php artisan migrate --force`, `php artisan db:seed --force`, and `php artisan storage:link`.
6. Clear `SEED_STAFF_PASSWORD` after the account exists. Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
7. Give the web process write access to `storage` and `bootstrap/cache`. Allow uploads slightly above 2 MB in PHP and the web server. Serve `/storage` as static files only.
8. Test login, CRUD, uploads, a sale, stock rejection, and history at the deployed URL.

For production SQLite, set `DB_DATABASE` to the absolute path of a persistent database file and create that file before migration. For MySQL, use InnoDB. Back up the database and uploaded files together.

Commit `composer.lock` after the first dependency installation. Do not commit `.env`, database files, or `vendor`.
