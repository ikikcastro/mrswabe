# Hotel Reservation System - Folder Structure

The application now separates authentication, customer, and owner routes into `routes/auth.php`, `routes/customer.php`, and `routes/admin.php`. Owner authorization uses Spatie roles and permissions through the `HasRoles` trait on `App\Models\User`.

Key additions include `AdminIdleTimeout`, username-based owner login, the username migration, permission tables, `RolesAndPermissionsSeeder`, and permission-aware admin form requests.

Blade views are grouped by purpose: the customer landing page is `resources/views/welcome.blade.php`, room browsing is in `resources/views/rooms/index.blade.php`, bookings are in `resources/views/bookings/index.blade.php`, and owner pages are in `resources/views/admin`. Shared components, layouts, and partials have their own folders under `resources/views`. Frontend assets remain organized under `resources/css` and `resources/js`.

Run `php artisan migrate` and `php artisan db:seed` after installing dependencies. Change the seeded owner password before deployment.
