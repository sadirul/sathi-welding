# Sathi Welding Karkhana

A mobile-first, Android-app-style client & payment manager built with Core PHP 8
(OOP, PDO), MySQL and vanilla JS — no framework.

## 1. Requirements

- XAMPP (Apache + MySQL) with PHP 8.1+
- Database name: `sathiwelding` (already created)

## 2. Setup

1. Project already lives at `htdocs/sathi-welding`, served by XAMPP.
2. Import the schema (pick one):
   - Quick: import `database/database.sql` via phpMyAdmin or:
     ```
     mysql -u root sathiwelding < database/database.sql
     ```
   - Incremental: run the files in `migrations/` in order (`001_...` → `004_...`).
   
   Both create `users`, `clients`, `transactions` and seed one login user.
3. If your MySQL uses a different host/user/password/db name, copy
   `.env.example` to `.env` and edit the values there — `config/config.php`
   loads it automatically (no server restart needed). A `.env` is already
   in place matching a stock XAMPP setup (`127.0.0.1`, user `root`, empty
   password, db `sathiwelding`); `.env` is git-ignored and blocked from
   direct web access, so it's safe to put real credentials in it.
4. Start Apache + MySQL from the XAMPP control panel.
5. Open **http://localhost/sathi-welding/** — Apache serves `index.php` at
   the project root directly, which redirects to `login.php` if you aren't
   logged in yet.

## 3. Default login

The seed migration creates one login with PIN **`103050`**.

**Change it immediately** — generate a new bcrypt hash and update the row:

```
php -r "echo password_hash('YOUR_NEW_6_DIGIT_PIN', PASSWORD_BCRYPT, ['cost' => 12]);"
```

Then:

```sql
UPDATE users SET pin_hash = 'PASTE_THE_HASH_HERE' WHERE id = 1;
```

## 4. App icon

The supplied logo is in place at `assets/icons/` (icon-32/48/96/192/512.png),
wired into the login logo, header account button, browser favicon and
`manifest.json`. To swap it later, drop a new square PNG at the same file
names/sizes — see `README.txt` in that folder.

## 5. Project structure

```
sathi-welding/                 # web root — point Apache/XAMPP here
├── config/        # DB credentials + app config (blocked from direct web access)
├── classes/       # OOP core: Database, Auth, Client, Transaction, Dashboard, Csrf, Validator, Response
├── api/           # JSON endpoints consumed by the frontend via fetch()
├── views/partials/# Shared header/sidebar markup included by the pages below
├── assets/        # CSS, JS, icons
├── database/      # Full schema (database.sql)
├── migrations/    # Same schema as numbered, incremental .sql files
├── login.php, index.php, clients.php, client-details.php   # pages
└── _bootstrap.php # shared page bootstrap (session, autoload)
```

## 6. Security notes

- PINs are hashed with `password_hash()`/bcrypt, never stored in plain text.
- Every `api/*.php` endpoint (other than login) requires an authenticated
  session (`Auth::requireAuth()`), and every state-changing endpoint also
  requires a valid CSRF token (`X-CSRF-Token` header, checked against the
  session-stored token rendered into each page's `<meta name="csrf-token">`).
- All SQL goes through PDO prepared statements.
- `config/`, `classes/`, `database/`, `migrations/` and `views/` each carry a
  `.htaccess` that denies direct web access; `_bootstrap.php` files (root and
  `api/`) are also blocked directly. Only the page files, `assets/` and
  `api/*.php` endpoints are reachable from a browser.
- Set the `APP_ENV=production` environment variable on your live server to
  suppress PHP error output (errors are still logged, never shown to users).
