# Session Security Checklist

For production, configure `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` on HTTPS, and `SESSION_SAME_SITE=lax`, then run `php artisan config:clear`.

The authentication controllers regenerate the session on login, logout invalidates the session and rotates the CSRF token, and `AdminIdleTimeout` logs owners out after 30 minutes of inactivity. Login, registration, and owner login routes are rate limited.
