# Hostinger Deployment Guide for admin.gadzio.co

This ERP is configured for your Hostinger account and domain:
- **Root Domain Folder**: `/home/u256101389/domains/admin.gadzio.co/`
- **Web Root Folder**: `/home/u256101389/domains/admin.gadzio.co/public_html/`
- **Application URL**: `https://admin.gadzio.co`

---

## 1. Directory Structure on Hostinger

To keep your application core, `.env` file, and database credentials secure from direct web access, place the files on Hostinger as follows:

```text
/home/u256101389/domains/admin.gadzio.co/
├── laravel_app/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
│
└── public_html/
    ├── assets/
    ├── storage/                 <-- (Symlink to laravel_app/storage/app/public)
    ├── .htaccess
    ├── favicon.ico
    ├── index.php
    └── robots.txt
```

---

## 2. Update `public_html/index.php` on Hostinger

In `/home/u256101389/domains/admin.gadzio.co/public_html/index.php`, ensure the paths point to `../laravel_app/`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../laravel_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../laravel_app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

---

## 3. Production `.env` on Hostinger

Create/update `/home/u256101389/domains/admin.gadzio.co/laravel_app/.env`:

```env
APP_NAME="Gadzio General trading LLC"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://admin.gadzio.co

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=daily
LOG_LEVEL=error

# Hostinger MySQL Database Credentials (Created in hPanel MySQL section)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u256101389_erp_db
DB_USERNAME=u256101389_erp_user
DB_PASSWORD=YourDatabasePassword

# Fast File-Based Cache & Session (Optimized for Shared Hosting)
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# Mail Configuration (Hostinger SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@gadzio.co
MAIL_PASSWORD=YourEmailPassword
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@gadzio.co"
MAIL_FROM_NAME="Gadzio General trading LLC"
```

---

## 4. SSH / Terminal Commands on Hostinger

In Hostinger hPanel, go to **Advanced > SSH Access** (or use the Terminal):

```bash
# 1. Navigate to your laravel_app directory
cd /home/u256101389/domains/admin.gadzio.co/laravel_app

# 2. Run Database Migrations & Performance Indexes
php artisan migrate --force

# 3. Create Storage Link (Connects storage/app/public to public_html/storage)
php artisan storage:link

# 4. Set directory write permissions for storage and bootstrap cache
chmod -R 775 storage bootstrap/cache

# 5. Compile and cache configurations, routes, and views for lightning fast loading
php artisan optimize
```

---

## 5. If You Don't Have SSH Access (Via File Manager)

If creating the symlink via SSH is not available, you can create the symlink manually:
1. In hPanel File Manager, open `/home/u256101389/domains/admin.gadzio.co/public_html/`.
2. Ensure there is a symlink or folder `storage` pointing to `/home/u256101389/domains/admin.gadzio.co/laravel_app/storage/app/public`.
3. To clear or rebuild caches without SSH, temporarily add a route or visit the settings page which automatically updates the cached configurations.
