# GuideMyPC

GuideMyPC is a PHP application for consumer technology support, including guides, categories, accounts, progress tracking, downloads, and community features. It currently runs locally with Apache, PHP, and MariaDB from XAMPP.

This repository is an early prototype being improved through the implementation tasks in [`Tasks/web-init/README.md`](Tasks/web-init/README.md). Do not expose the local XAMPP installation or its database tools to the public internet.

## Supported Local Environment

- Windows 10 or Windows 11
- XAMPP with PHP 8.2.x, Apache 2.4.x, and MariaDB 10.4.x
- Git
- A local checkout at `C:\xampp\htdocs\GuideMyPC`

PHP must have these extensions enabled in `C:\xampp\php\php.ini`:

- `mysqli`
- `mbstring`
- `openssl`
- `fileinfo`
- `json`
- `curl`

Composer is not currently required. If task `004-application-structure-and-error-handling.md` introduces it, use the committed `composer.json` and lock file rather than installing untracked packages.

## Quick Start

1. Install XAMPP with Apache, MySQL/MariaDB, and PHP 8.2 enabled.
2. Clone the repository into `C:\xampp\htdocs\GuideMyPC`.
3. Copy `.env.example` to `.env` and keep `.env` private.
4. In the XAMPP Control Panel, start Apache and MySQL.
5. Run the setup check:

   ```powershell
   C:\xampp\php\php.exe scripts\check-local-setup.php
   ```

6. Create the schema and, optionally, safe sample content:

    ```powershell
    C:\xampp\php\php.exe database\migrate.php
    C:\xampp\php\php.exe database\seed.php
    ```

7. Open [http://localhost/GuideMyPC/](http://localhost/GuideMyPC/).

`config.php` loads database settings from the untracked `.env` file. Keep that file local and use the setup checker before troubleshooting application pages.

## Database Setup

Task `003-database-migrations-and-seeds.md` owns the versioned migration runner and safe sample-content seed. A new installation uses:

```powershell
C:\xampp\php\php.exe database\migrate.php
C:\xampp\php\php.exe database\seed.php
```

The migration command creates the configured database when the local account has permission. The seed command is optional and only adds public sample content. The legacy mutable prototype dump was removed because it contained production-like sample data below the public web root. See [`database/README.md`](database/README.md) for migration naming, idempotence, rollback, and local test-database guidance.

## Configuration

Copy the template before making local changes:

```powershell
Copy-Item .env.example .env
```

Use a local-only database account where possible. XAMPP's `root` account with an empty password is a development default, not a production configuration. Never commit `.env`, passwords, API keys, database exports, uploads, or logs.

The checker reads these values from `.env`:

- `APP_ENV`
- `APP_URL`
- `APP_PRIVATE_PATH` (optional; defaults outside Apache's document root)
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

It prints only actionable status and never prints the password or connection string. To test a failed connection, temporarily set `DB_NAME` in your local `.env` to a nonexistent name, run the checker, then restore it.

## Optional Virtual Host

The default URL works when the checkout is under `htdocs`. To use a local hostname instead, add a virtual host to an Apache configuration file enabled by your XAMPP installation:

```apache
<VirtualHost *:80>
    ServerName guidemypc.test
    DocumentRoot "C:/xampp/htdocs/GuideMyPC"

    <Directory "C:/xampp/htdocs/GuideMyPC">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 guidemypc.test` to the Windows hosts file, restart Apache, and set `APP_URL=http://guidemypc.test` in `.env`. This is local development only. Later security work will move private configuration, logs, and uploads outside the public web root.

## Local File Access and Mail

- Keep the checkout readable by the Windows user running Apache; do not grant broad write access such as `Everyone:Full Control`.
- Do not use public writable directories for logs, backups, or uploads. These paths are ignored locally and will be restructured by the security bootstrap task.
- XAMPP does not configure outbound mail safely by default. Do not assume registration, password-reset, or contact email will be delivered in local development. Mail transport is a later feature and must use test accounts only.

## Reset Local Data

Resetting deletes all local GuideMyPC data. Confirm that the selected server is local and make a backup before continuing.

```powershell
C:\xampp\mysql\bin\mysqldump.exe -u root -p guidemypc > local-guidemypc-backup.sql
C:\xampp\mysql\bin\mysql.exe -u root -p -e "DROP DATABASE guidemypc; CREATE DATABASE guidemypc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Rerun the documented migration and optional seed commands. Do not store the backup inside this repository or commit it.

## Troubleshooting

| Problem | Check | Resolution |
| --- | --- | --- |
| Apache will not start | Port 80 or 443 is in use | Run `netstat -ano | findstr :80` or `netstat -ano | findstr :443`, stop the conflicting local service, or configure a different local Apache port. |
| MariaDB will not start | XAMPP MySQL logs and port 3306 | Stop conflicting MySQL/MariaDB services, inspect `C:\xampp\mysql\data\mysql_error.log`, and do not delete database files without a backup. |
| Required PHP extension is missing | `C:\xampp\php\php.exe -m` | Enable the extension in `C:\xampp\php\php.ini`, then restart Apache and rerun the setup check. |
| Setup check cannot connect | `.env`, XAMPP MySQL status, database name | Confirm MySQL is running, `guidemypc` exists, and the local account can access it. The checker intentionally does not reveal credential values. |
| Browser shows the wrong site | Checkout location or virtual host | Confirm the folder is `C:\xampp\htdocs\GuideMyPC` and use `http://localhost/GuideMyPC/`, or review the optional virtual-host setup. |

## Development Roadmap

- [`001-xampp-local-setup.md`](Tasks/web-init/001-xampp-local-setup.md): local setup and this documentation
- [`002-security-bootstrap.md`](Tasks/web-init/002-security-bootstrap.md): environment-backed configuration and security baseline
- [`003-database-migrations-and-seeds.md`](Tasks/web-init/003-database-migrations-and-seeds.md): reproducible migrations and sanitized seeds
- [`Tasks/web-init/README.md`](Tasks/web-init/README.md): full release sequence
