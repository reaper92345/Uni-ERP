## Inventory Management Web App

This project is a simple inventory, sales, and purchasing web application built with PHP (Apache), MySQL, and a little JavaScript and CSS. It includes Docker files so you can run everything easily on Windows, macOS, or Linux.

### Main Goals
- Keep track of products, sales, purchases, and expenses
- Provide basic ERP-style modules (sales, purchases, reports, etc.)
- Be easy to start locally using Docker

---

## Features
- PHP 8.2 with Apache
- MySQL 8 + phpMyAdmin included
- Ready-to-use Docker setup for dev and prod
- Modules for sales, purchases, reports, and more (see `pages/modules/`)

---

## Requirements
- Docker Desktop installed
- Git (optional, but helpful)
- On Windows: PowerShell (already available)

---

## Quick Start (Docker)

You can use the provided compose files to run the app quickly.

1) Copy environment file
```bash
cp env.example .env
```
Edit `.env` if you need to change ports or credentials.

2) Start the development environment (with Xdebug)
```bash
docker-compose -f docker-compose.dev.yml up -d
```

Alternatively:
- Production-like: `docker-compose -f docker-compose.prod.yml up -d`
- Default: `docker-compose up -d`

3) Open in your browser
- App: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081` (default user: `inventory_user`, password: `inventory_password`)

4) Useful commands
```bash
# See logs
docker-compose logs -f

# Enter the PHP container
docker exec -it inventory_app bash

# Stop everything
docker-compose down

# Rebuild images
docker-compose build --no-cache
```

On Windows, you can also double-click `start.bat` to bring everything up.

---

## Project Structure
```
inventory/
  api/                # PHP endpoints (auth, reports, etc.)
  assets/             # CSS and JS
  charts/             # Simple chart pages
  includes/           # Shared header, footer, config
  pages/              # Main pages and modules
  index.php           # Landing page
  database.sql        # Database schema/seed
  Dockerfile*         # Docker images
  docker-compose*.yml # Docker orchestration files
```

Key files/directories:
- `includes/config.php`: App configuration and DB connection
- `database.sql`: Initial MySQL schema (imported automatically by Docker)
- `api/`: Endpoints like `auth-google.php`, `reports.php`, etc.
- `pages/`: Top-level pages and ERP-like modules under `pages/modules/`

---

## Configuration

Environment variables are managed via `.env`. Start by copying `env.example` to `.env`.

Common variables:
- `APP_PORT` (default 8080)
- `PHPMYADMIN_PORT` (default 8081)
- `MYSQL_ROOT_PASSWORD`
- `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`

Update these in `.env` if you need different credentials or ports.

---

## Database
- MySQL 8 is used
- The container will load `database.sql` on first run
- You can manage the DB through phpMyAdmin at `http://localhost:8081`

Default phpMyAdmin credentials (if unchanged):
- Username: `inventory_user`
- Password: `inventory_password`

---

## How to Develop
1) Start dev environment with Xdebug:
```bash
docker-compose -f docker-compose.dev.yml up -d
```
2) Edit PHP/JS/CSS files locally; changes reflect immediately (no rebuild needed)
3) If you add PHP extensions or base image changes, rebuild:
```bash
docker-compose build --no-cache && docker-compose up -d
```

Code locations:
- PHP pages: `pages/`, `pages/modules/`, `index.php`
- Shared templates: `includes/header.php`, `includes/footer.php`
- Static assets: `assets/css/style.css`, `assets/js/main.js`
- API endpoints: `api/`

---

## Modules Overview (brief)
- Sales: `pages/sales.php`, `pages/modules/sales_*`
- Purchases: `pages/purchases.php`, `pages/modules/purchase_*`
- Products: `pages/products.php`
- Expenses: `pages/expenses.php`
- Reports & Charts: `pages/reports.php`, `charts/sales_vs_expenses.php`
- ERP Group: `pages/erp.php` with submodules like `crm.php`, `hr_payroll.php`, etc.

Note: Some modules are placeholders or minimal implementations to help you extend the app.

---

## Authentication

Google OAuth endpoints exist in `api/auth-google.php` and `api/auth-google-callback.php`. For setup details and scopes, see `AUTH_README.md`.

To disable Google auth and use a simple local login, you can replace the auth logic in `includes/config.php` and related pages with your own checks. This app is kept simple on purpose, so you can adapt it to your course or internship needs.

---

## Troubleshooting
- Port in use: Change `APP_PORT`/`PHPMYADMIN_PORT` in `.env` and restart
- DB not initializing: Remove the DB volume and re-up: `docker-compose down -v && docker-compose up -d`
- White page / PHP errors: Check logs with `docker-compose logs -f` and enable error display in dev
- Cannot connect to DB: Verify variables in `.env` match `includes/config.php`

---

## License
This project is shared for learning purposes. You can modify it freely for your studies. If you use it in production, review security, backups, and performance settings first.

---

## Credits
Made with PHP, MySQL, and Docker. Structured to be easy for students to understand and extend.


