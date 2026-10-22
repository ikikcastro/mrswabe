# Composer Package

The project requires `spatie/laravel-permission` for roles and permissions. Install dependencies with `composer install`, then run `php artisan migrate` and `php artisan db:seed`.

Roles and permissions are defined in `RolesAndPermissionsSeeder`; the owner role manages rooms, reservations, and reports.
