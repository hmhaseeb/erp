# Hostinger Shared Hosting Deployment Guide

This document provides step-by-step instructions for deploying the **Small Business Accounting & Inventory ERP** to **Hostinger Shared Hosting**.

---

## 1. Hosting Requirements Checklist

* **PHP Version**: PHP 8.2 or PHP 8.3
* **PHP Extensions**: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`, `zip`, `gd`, `fileinfo`
* **Database**: MySQL / MariaDB
* **Web Server**: Apache or LiteSpeed (standard on Hostinger)

---

## 2. Directory Structure on Hostinger

Recommended deployment structure on Hostinger File Manager:

```text
/home/u123456789/
├── laravel-app/             <-- Core Laravel files (OUTSIDE public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
│
└── domains/yourdomain.com/public_html/   <-- Contents of Laravel's `public/` folder
    ├── index.php
    ├── assets/
    ├── storage/
    └── .htaccess
```

---

## 3. Deployment Steps

### Step 1: Upload Files
1. Compress project files into a `.zip` archive (excluding `node_modules` or `.git`).
2. Upload and extract into `/home/u123456789/laravel-app/`.
3. Move all contents of `/laravel-app/public/` to `/domains/yourdomain.com/public_html/`.

### Step 2: Update `public_html/index.php`
Edit `public_html/index.php` to point paths back to `laravel-app`:

```php
// Update maintenance file check path
if (file_exists($maintenance = __DIR__.'/../../laravel-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Update Composer autoloader path
require __DIR__.'/../../laravel-app/vendor/autoload.php';

// Update Laravel bootstrap path
$app = require_once __DIR__.'/../../laravel-app/bootstrap/app.php';
```

### Step 3: Configure Database & `.env`
1. Create a MySQL database and database user in Hostinger cPanel / hPanel.
2. Copy `.env.example` to `.env` inside `laravel-app/`.
3. Fill in your Hostinger database details:

```env
APP_NAME="Small Business ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_erp
DB_USERNAME=u123456789_erpuser
DB_PASSWORD=YourSecurePasswordHere

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

### Step 4: Run Migrations & Storage Link
Via Hostinger SSH or Terminal:

```bash
cd /home/u123456789/laravel-app
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
```

*(If symlink is restricted, create a shortcut from `public_html/storage` to `laravel-app/storage/app/public`)*

---

## 4. Default Login Credentials

* **URL**: `https://yourdomain.com/login`
* **Email**: `admin@erp.com`
* **Password**: `admin123` *(Change password immediately in settings!)*
