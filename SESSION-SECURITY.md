# Session Security Checklist

For production, configure a stable `APP_KEY` and set `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` on HTTPS, and `SESSION_SAME_SITE=lax`. Run `php artisan migrate --force` before serving traffic so the sessions table exists. Never generate a new `APP_KEY` each time the app starts, as existing session cookies will no longer decrypt.

The authentication controllers regenerate the session on login, logout invalidates the session and rotates the CSRF token, and `AdminIdleTimeout` logs owners out after 30 minutes of inactivity. Login, registration, and owner login routes are rate limited.
