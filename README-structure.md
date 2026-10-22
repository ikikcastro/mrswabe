# Hotel Reservation System - Folder Structure

The application now separates authentication, customer, and owner routes into `routes/auth.php`, `routes/customer.php`, and `routes/admin.php`. Owner authorization uses Spatie roles and permissions through the `HasRoles` trait on `App\Models\User`.

Key additions include `AdminIdleTimeout`, username-based owner login, the username migration, permission tables, `RolesAndPermissionsSeeder`, and permission-aware admin form requests.

Run `php artisan migrate` and `php artisan db:seed` after installing dependencies. Change the seeded owner password before deployment.
