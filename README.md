# Stockroom — Laravel CRUD demo

A complete local product manager using Laravel 12, Blade, Bootstrap 5, jQuery,
and Yajra DataTables 12. No NPM, Node, Vite, or frontend build is required.

## First-time setup after copying the complete project

Use **Windows PowerShell**. Copy the complete Laravel source, including `artisan`,
`composer.json`, `composer.lock`, `.env.example`, `app`, `bootstrap`, `config`,
`database`, `public`, `resources`, `routes`, `storage`, and `tests`.
Keep the folder structure and hidden files. Copying only Blade views or controllers
is not enough: Laravel's framework scaffold and Composer files are also required.

For a fresh installation, omit `vendor`, `.env`, the `public/storage` link, and
`database/database.sqlite` from the copied files. The commands below install dependencies
and create a new environment/database. The seeder generates the sample product images.
If you want to preserve an existing catalog instead, also preserve its database,
uploaded files, and original application key; use the existing-project commands below.

Prerequisites: PHP 8.2+, Composer 2, and PHP extensions including PDO SQLite, GD,
Fileinfo, Mbstring, OpenSSL, XML, and Zip. XAMPP's PHP is used below.
Composer must already be installed and available in PowerShell. Internet access
is needed for Composer packages and the frontend CDN assets.

**Fresh copy only:** paste this entire block into PowerShell. If you copied the project
somewhere else, change the first line. These are direct terminal commands; no setup
script files, NPM, Node, or Vite commands are required.

```powershell
cd C:\xampp\htdocs\laravel_crud_demo
$env:Path = "C:\xampp\php;$env:Path"
php -v
composer --version
composer install --no-interaction --prefer-dist
composer check-platform-reqs
Copy-Item -LiteralPath .env.example -Destination .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan config:clear
php artisan key:generate
php artisan migrate
php artisan optimize:clear
php artisan storage:link
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8001
```

Stop at the first error and fix it before continuing. The final command stays running
in the terminal; that is expected. Keep that terminal open while using the project.
Press **Ctrl+C** to stop the server.

Why this order:

| Command | Purpose |
| --- | --- |
| `composer install` | Installs the exact backend dependencies from `composer.lock` |
| `composer check-platform-reqs` | Checks the installed PHP version and required extensions |
| `Copy-Item ...` | Creates the fresh local `.env` using the portable SQLite configuration |
| `php -r ...` | Creates the SQLite file only if it is missing |
| `php artisan config:clear` | Removes cached settings before using the new environment |
| `php artisan key:generate` | Generates the fresh installation's application key |
| `php artisan migrate` | Creates users, sessions, cache, password-reset, and product tables |
| `php artisan optimize:clear` | Clears remaining caches after their database tables exist |
| `php artisan storage:link` | Makes public uploads available through `public/storage` |
| `php artisan db:seed` | Adds the verified demo account, 16 products, and sample PNG images |
| `php artisan serve ...` | Starts the local Laravel server on port 8001 |

Do not rerun the fresh-copy block against an existing installation: copying `.env.example`
over `.env` replaces its configuration, and generating a new key invalidates existing
sessions and signed links. Use the next block for the project already built here.
Do not copy a `public/storage` link from another project path; create it locally.

## Run this existing project / start it again later

The project at `C:\xampp\htdocs\laravel_crud_demo` is already installed and seeded.
For normal use, paste only:

```powershell
cd C:\xampp\htdocs\laravel_crud_demo
$env:Path = "C:\xampp\php;$env:Path"
php artisan serve --host=127.0.0.1 --port=8001
```

Open **http://127.0.0.1:8001**. Use this exact host consistently.
If the project is already running on port 8001, just open that URL; do not start a
second server. After Ctrl+C, run the same `php artisan serve` command to start it again.

Demo account: **demo@example.com** / **DemoPass!2026**.
This is a public local-only demonstration credential, not a production account.
The account is preverified and has 16 sample products with original local PNG illustrations.

The default database is **SQLite**, stored at `database/database.sqlite`, so no MySQL
server is needed. `.env.example` uses Laravel's project-relative default database path,
which works when a fresh copy is placed in another folder. If reusing an old `.env`
that contains an absolute `DB_DATABASE` path, update that path to the copied database.
Frontend CDNs and Google Fonts require internet access.

## Features

- Register, log in/out, remember me, email verification, forgot/reset password, change password.
- Session regeneration, CSRF protection, login throttling, hashed passwords.
- Owner-scoped product data and policies protecting view/edit/update/delete endpoints.
- Complete product CRUD and private catalog statistics.
- Required/string/email/unique/password/confirmed/accepted rules for authentication.
- Product numeric, decimal precision, integer, range, allowed status, conditional date,
  cross-field sale price and stock validation, custom SKU rule, per-owner unique SKUs.
- Images: JPG/JPEG, PNG, WebP; up to 2 MB; 100–4000 pixels on each axis.
- Preview, retain image, replace image, remove image, clean up on product deletion.
- Database transactions and cleanup of new uploads after a failed database write.
- Yajra server-side searching, numeric sorting, pagination, status filter, and action buttons.
- Responsive Bootstrap UI with accessible labels, errors, and escaped user content.

## New accounts and local email

Register at `/register`. New accounts must verify their email before using products.
`MAIL_MAILER=log` writes verification and password-reset emails to:

```text
storage/logs/laravel.log
```

Watch new emails from another terminal:

```powershell
Get-Content .\storage\logs\laravel.log -Tail 100 -Wait
```

Copy the complete URL from the logged email into the same browser. Long links may
wrap across lines. Email links are not exposed on the page because that would bypass
verification in a real mail configuration. Reset your password while logged out.
To deliver real mail, configure `MAIL_MAILER=smtp` and your provider's settings in `.env`.

## Validation examples

Create an active product: name `Ceramic cup`, SKU `PRD-CUP002`, price `499.00`,
sale price `449.00`, stock `10`, publication date today, and a valid image.

- SKU must be PRD- plus 4–20 letters/numbers; input is normalized to uppercase.
- The same SKU is allowed for different users, but not twice for one user.
- Active products need positive stock and a publication date no later than today.
- Sale price must be below regular price. Zero sale price is supported.
- Create requires an image. Update without a file retains the old image.
- Replacement and removal cannot be selected together.
- Browsers clear file inputs after validation failures; select the upload again.
- Images are public assets even though catalog records are owner-protected.

## Structure and learning order

1. `routes/web.php`: guest/auth/verified middleware and resource routing.
2. `database/migrations/2026_10_30_000001_create_products_table.php`: schema and constraints.
3. `app/Models/User.php`, `Product.php`: relationships, mass assignment, casts.
4. `app/Policies/ProductPolicy.php`: owner authorization.
5. `app/Rules/ProductSku.php`: custom validation rule.
6. `app/Http/Requests/ProductRequest.php`: authorization, normalization, validation.
7. `app/Services/ProductService.php`: file lifecycle and database transactions.
8. `app/Http/Controllers/AuthController.php`, `ProductController.php`: request coordination.
9. `resources/views/layouts/app.blade.php`: shared CDN layout.
10. `resources/views/components/input.blade.php`: reusable accessible field.
11. `resources/views/auth/`: authentication forms.
12. `resources/views/products/`: list/form/details and escaped HTML cell templates.
13. `public/css/app.css`, `public/js/app.js`: build-free frontend assets.
14. `tests/Feature/`: isolated authentication, CRUD, validation, storage, and DataTables tests.

## Development and verification commands

Run these in a **second PowerShell terminal**, from the project folder. They are
optional maintenance/check commands, not additional steps needed every time you start.

```powershell
cd C:\xampp\htdocs\laravel_crud_demo
$env:Path = "C:\xampp\php;$env:Path"
composer install --no-interaction --prefer-dist
php artisan config:clear
php artisan migrate
php artisan optimize:clear
php artisan test
php artisan route:list
```

To add the demo records again if any are missing:

```powershell
php artisan db:seed
```

The demo seeder is additive and skips existing SKUs; it does not reset changed passwords.
Tests use an in-memory SQLite database and fake storage, not your demo records/uploads.
`storage:link` is needed once per installation, or when repairing a missing link.
An "already exists" result is normal when the link has already been created.
Never use `migrate:fresh` on a database whose data you need to keep.

## Alternative: reconstruct from application code instead of copying the full project

Only use this route if you do not have the complete Laravel project source.
Run these commands **before** pasting your application files. The destination must
not exist or must be empty; do not run `create-project` over this completed project.

```powershell
cd C:\xampp\htdocs
$env:Path = "C:\xampp\php;$env:Path"
composer create-project laravel/laravel laravel_crud_demo "12.*" --no-interaction --prefer-dist
cd C:\xampp\htdocs\laravel_crud_demo
composer require "yajra/laravel-datatables-oracle:12.*" --no-interaction
```

Now copy all the custom application files and migrations into the matching paths.
Include this completed project's `composer.json`, `composer.lock`, and `.env.example`
when reproducing this exact project; its Composer commands do not invoke frontend tools.
Once the source files are in place, run the **first-time setup** block above.
Do not run `npm install`, `npm run build`, or the unmodified scaffold's frontend setup commands.

## Optional: switch to MySQL / MariaDB

Start MySQL in XAMPP. Create a separate database in phpMyAdmin:

```sql
CREATE DATABASE laravel_crud_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Replace the database values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_crud_demo
DB_USERNAME=root
DB_PASSWORD=
```

Set the actual password for your account. Stop the development server before switching,
then run the commands below. Migrations run before clearing the database cache:

```powershell
cd C:\xampp\htdocs\laravel_crud_demo
$env:Path = "C:\xampp\php;$env:Path"
php artisan config:clear
php artisan migrate
php artisan optimize:clear
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8001
```
Switching connections does not transfer existing SQLite records automatically.

## Troubleshooting

- Images missing: run `php artisan storage:link`, check `APP_URL`, and keep the same host.
- Windows link permissions: enable Developer Mode or use a terminal permitted to create links.
- 419: refresh; check cookies and database sessions; do not mix localhost and 127.0.0.1.
- DataTables errors: inspect `/products/data` in Network tools and `storage/logs/laravel.log`.
- Upload limits: `php --ini`; use `upload_max_filesize=5M`, `post_max_size=8M`; restart PHP.
- Invalid signed link: copy the complete URL and request a new one; do not change APP_KEY.
- Port 8001 occupied: stop the old server or consistently update APP_URL and serve port.
- SQLite has no rows: confirm `DB_DATABASE` points at this project's database file.

## Scope and deployment

This is a learning demo, not an admin system: each user sees only their own products.
Password changes rotate remember-me tokens but do not terminate all existing sessions
on other devices. There is no account deletion, two-factor authentication, or roles UI.
Filesystem cleanup failures are logged; unlike database writes, disk operations cannot
be rolled back atomically. A production app should reconcile orphan files periodically.

For deployment use HTTPS, `APP_DEBUG=false`, real SMTP, secure session cookies,
backups, and a web-server document root pointing only at `public/`. Remove the public
demo credentials/account and demo hint. Do not serve the project root publicly or expose `.env`.
No site has been published by this local setup.
