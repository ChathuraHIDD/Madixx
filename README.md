# MADIXX

Sunglasses, spectacles & eyewear accessories e-commerce site. PHP 8.1 + MySQL, no frameworks.

## Local setup (WAMP/XAMPP/MAMP)

1. Apache should be wired to PHP 8.1+.
2. Create and seed the `madixx` database from `database/madixx.sql`:
   `mysql -u root < database/madixx.sql`
3. This project folder must be named `Madixx` inside your server's document root (e.g. `htdocs/Madixx` or `www/Madixx`) — `includes/init.php` sets `BASE_URL` to `/Madixx/` to match.
4. Site: http://localhost/Madixx/
5. Admin panel: http://localhost/Madixx/admin/login.php

## Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@madixx.com | Madixx@2026 |
| Customer | sophie@example.com | Password@123 |
| Customer | amaya@example.com | Password@123 |

## Product imagery

- Logo: `assets/images/logo.png`
- Hero banner: `assets/images/hero.png`
- Products: `assets/images/p1.png` – `p24.png` (10 sunglasses, 9 spectacles, 5 accessories — see `database/madixx.sql` for the mapping)

Manage/replace any of these via the admin panel's image upload once new photography is available.

## Notes

- No outbound mail server is configured: the "forgot password" flow displays the reset link directly on-screen instead of emailing it.
- Payment is Cash on Delivery / Bank Transfer only, structured so an online gateway can be added later without schema changes (`orders.payment_method`).
- `includes/init.php` sets `APP_DEBUG = false` (errors are logged, not displayed). Flip to `true` locally if you need on-page error output while developing.
- The `products.skin_type` database column is inherited from the original template and repurposed to store each product's **Frame Material** (Acetate, Metal, Titanium, etc.) — it is labelled "Frame Material" everywhere on the storefront and in the admin panel.
