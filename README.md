# Flex DB Dump

Laravel database backup & download package with **mysqldump** or **PHP-only exporter**.

## Features
- SQL dump (CLI)
- PHP-only exporter (shared hosting friendly)
- Config based mode switching
- Chunked export
- Auto download
- Secure & lightweight

## Installation
```bash
     composer require daiyan_mozumder/flex-db-dump
```

## Publish Config
```bash
     php artisan vendor:publish --tag=flex-db-dump-config
```

## Configuration
```bash
     FLEX_DB_DUMP_MODE=php
```

## Supported modes:

  php → No CLI, pure PHP

  sql → Uses mysqldump

## Route
```bash
     GET /flex/db-dump
```

## Protect it:
```bash
     Route::middleware(['auth', 'role:admin'])->group(...)
```

## Usage

    Visit:
```bash
    /flex/db-dump
```
 Your database will download automatically.

## Requirements

 PHP 8.1+
 Laravel 10+
 mysqldump (for sql mode)

## Security

 ⚠️ Always restrict route access (admin only).

## 🖥 Server Requirements for mysqldump Mode (VPS / VFS)

   When using sql mode, flex-db-dump relies on the system-level mysqldump binary.
   This does NOT work automatically unless your server is properly configured.

## ✅ Supported Server Types

   1. VPS (DigitalOcean, AWS EC2, Hetzner, Linode, etc.)
   2. Dedicated server
   3. Docker-based servers
   4. Self-managed cloud servers

## ⚠️ Not supported on most shared hosting environments.

## 1️⃣ Verify mysqldump Is Installed

   Run this on your server:

   Windows:
```bash
    mysqldump --version
```
Expected output:
```bash
    mysqldump  Ver 8.0.30 for Win64 on x86_64 (MySQL Community Server - GPL)
```

   MacOS
```bash
    which mysqldump
```
   Expected output:
```bash
    /usr/bin/mysqldump
```

  If nothing is returned, install it.

## 2️⃣ Install MySQL Client (If Missing)

   Ubuntu / Debian

```bash
    sudo apt update
    sudo apt install mysql-client -y
```

   CentOS / Rocky / Alma

```bash
    sudo yum install mysql -y
```

## 3️⃣ Ensure PHP Can Execute System Commands

   The following PHP functions must NOT be disabled:

   1. proc_open 
   2. shell_exec 
   3. exec

   Check:
```bash
    php -i | grep disable_functions
```

   If disabled, update php.ini:
```bash
    disable_functions =
```
   Then restart PHP:
```bash
    sudo systemctl restart php8.2-fpm
```

## 4️⃣ MySQL Credentials Access (IMPORTANT)
   Option A: Use Laravel .env (Default)
```bash
  DB_HOST=127.0.0.1
  DB_DATABASE=your_db
  DB_USERNAME=your_user
  DB_PASSWORD=your_password
```

  ⚠️ Some MySQL versions block passwords passed via CLI.

## Option B (RECOMMENDED): Use .my.cnf (Most Secure)

   Create this file:
```bash
  nano ~/.my.cnf
```

  Add:

  [mysqldump]
```bash
  user=your_db_user
  password=your_db_password
  host=127.0.0.1
```

## Set permissions:
```bash
   chmod 600 ~/.my.cnf
```

   Then remove password from CLI usage automatically — mysqldump will read it securely.

   ✅ This avoids password exposure in process lists.

## 5️⃣ SELinux (CentOS Only)

   If SELinux is enabled:
```bash
setsebool -P httpd_can_network_connect_db 1
```

## 6️⃣ Storage Permissions

   Ensure Laravel can write backups:

```bash
   chmod -R 775 storage
   chown -R www-data:www-data storage
```

## 7️⃣ Test mysqldump Manually

   Before using the package:

```bash
   mysqldump your_db > test.sql
```

  If this fails, the package will fail too.

## 8️⃣ Enable SQL Mode in Package
    FLEX_DB_DUMP_MODE=sql

## 9️⃣ When to Use php Mode Instead

   Use PHP exporter mode if:

   1. Shared hosting 
   2. No shell access 
   3. proc_open disabled 
   4. No mysqldump available

```bash
   FLEX_DB_DUMP_MODE=php
```

## 🔐 Security Recommendations

    1. Always protect /flex/db-dump route
    2. Restrict to admin users only 
    3. Add rate limiting 
    4. Never expose publicly
    
## Example:

```bash
  Route::middleware(['auth', 'role:admin', 'throttle:1,10'])->group(function () {
  Route::get('flex/db-dump', [\Flex\DbDump\Http\Controllers\DbDumpController::class, 'download']);
  })->name('flex_db_dump');
```

## Author

    Daiyan Mozumder

## License

    MIT
