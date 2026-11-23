# TaskFlow

TaskFlow is a Laravel application for managing personal tasks. Each account has a private dashboard with a searchable task list, a monthly calendar, and upcoming deadlines.

## Requirements

- PHP 8.2 or later with the PDO MySQL extension (`pdo_mysql`).
- Composer 2.
- MySQL or MariaDB, for example the database server bundled with XAMPP. phpMyAdmin is available at <http://localhost/phpmyadmin/> in a default XAMPP installation.

Laravel serves the JavaScript and CSS from `public/`. The local setup uses PHP and Composer.

## Run locally

1. Start the database server. Open phpMyAdmin and create a database named `taskflow` using the `utf8mb4_unicode_ci` collation.
2. Choose how to populate it. For a demo, select the empty `taskflow` database in phpMyAdmin, then use **Import** to load `database.sql`. For a blank app, leave the database empty and let Laravel create the tables.
3. Make a local `.env` from `.env.example` and update the database credentials if they differ from the local XAMPP defaults. Run the following commands once for initial setup.

   ```sh
   composer setup
   php artisan serve
   ```

4. Open <http://127.0.0.1:8000>. Register an account or sign in with the imported demo account. On an empty installation, you can add the demo with `php artisan db:seed` once.

The demo username is `demo`, the email is `demo@taskflow.test`, and the phone is `+15550000001`. The password is `password`. The SQL export contains the schema, migration history, and sample data. Import it into an empty database.

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

## Project proposal

Open `proposal.docx` for the scope, design diagrams, working tree, three-week schedule, and evaluation criteria.
