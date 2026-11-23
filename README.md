# TaskFlow

[Live website](https://taskflows.free.nf/)

TaskFlow is a Laravel application for managing personal tasks. Each account has a private dashboard with a searchable task list, a monthly calendar, and upcoming deadlines.

## Requirements

- PHP 8.2 or later with the PDO MySQL extension (`pdo_mysql`).
- Composer 2.
- MySQL or MariaDB, for example the database server bundled with XAMPP. phpMyAdmin is available at <http://localhost/phpmyadmin/> in a default XAMPP installation.

Laravel serves the JavaScript and CSS from `public/`. The local setup uses PHP and Composer.

## Run locally

1. Start MySQL in XAMPP. To use phpMyAdmin, also start Apache and open <http://localhost/phpmyadmin/>. Create a database named `taskflow` with the `utf8mb4_unicode_ci` collation.
2. Open a terminal in the project folder. Install dependencies and create your local environment file. The second command copies the template only if `.env` is missing.

   ```sh
   composer install
   php -r "file_exists('.env') || copy('.env.example', '.env');"
   ```

3. In `.env`, set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, and `DB_DATABASE=taskflow`. Set `DB_USERNAME` and `DB_PASSWORD` to your local MySQL credentials. The template uses the default local XAMPP account. Run `php artisan key:generate` once if `APP_KEY` is blank.
4. For a blank app, run `php artisan migrate`. To start with sample tasks, use **Import** in phpMyAdmin to load `database.sql` into the empty **taskflow** database. The import includes the migrations and sample data. For sample tasks on an already migrated, empty installation, run `php artisan db:seed` once.
5. Run `php artisan serve` and open <http://127.0.0.1:8000>. Register an account or sign in with the local demo account.

The local demo username is `demo`, the email is `demo@taskflow.test`, and the phone is `+15550000001`. The password is `password`. Use this account for local testing.

## Dashboard design

`app/Queries/DashboardTaskQuery.php` implements the Query Object pattern. It starts from the signed-in user's tasks and applies dashboard search, filters, and sorting in one place. The controller uses the returned collection to derive the list, calendar, and upcoming deadlines while keeping the overall dashboard counts independent of those filters.

## Try password recovery in the local demo

Set `TASKFLOW_DEMO_PASSWORD_RESET=true` in `.env` and use **Forgot password?** on the sign-in screen. Enter the demo account identifier, copy the six-digit code, and choose a new password. The code expires after ten minutes and can be used once. This demo is disabled by default and its routes are unavailable in production. Production recovery requires a verified delivery channel.

## Run tests

Create a separate empty database named `taskflow_test` in phpMyAdmin using the same collation. Grant your test credentials access to it. The test suite rebuilds that dedicated test database.

```sh
composer test
```

The tests use the local MySQL connection settings from `.env`. To use different credentials for tests, set them in a local `.env.testing` and provide an application key there. PHPUnit selects `taskflow_test`.
