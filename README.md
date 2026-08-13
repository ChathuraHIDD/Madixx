# Glowelle

Premium cosmetics & skincare e-commerce site. PHP 8.1 + MySQL, no frameworks.

## Local setup (WAMP)

1. Apache is wired to PHP 8.1.31 (was 7.4.33 — switched in `httpd.conf`, backups saved alongside as `*.bak-php74`).
2. Database `glowelle` is created and seeded — see `database/glowelle.sql` to re-import if needed:
   `mysql -u root < database/glowelle.sql`
3. Site: http://localhost/Glowelle/
4. Admin panel: http://localhost/Glowelle/admin/login.php

## Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@glowelle.com | Glowelle@2026 |
| Customer | sophie@example.com | Glowelle@2026 |
| Customer | amaya@example.com | Glowelle@2026 |

## Notes

- Product/blog imagery uses generated placeholder graphics (`assets/images/placeholder-*.svg`) — replace via the admin panel's image upload once real photography is available.
- No outbound mail server is configured: the "forgot password" flow displays the reset link directly on-screen instead of emailing it.
- Payment is Cash on Delivery / Bank Transfer only, structured so an online gateway can be added later without schema changes (`orders.payment_method`).
- `includes/init.php` sets `APP_DEBUG = false` (errors are logged to `wamp64/logs/php_error.log`, not displayed). Flip to `true` locally if you need on-page error output while developing.
