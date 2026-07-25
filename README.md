# GuideMyPC

GuideMyPC is a PHP application for consumer technology support. The verified-core release includes public Guides, Knowledge, approved Downloads, Search, Diagnostics, accounts and progress, role-scoped Dashboard summaries and charts, and the canonical legacy Community post/comment/like workflow. It runs locally with Apache, PHP, and MariaDB from XAMPP.

The release keeps the canonical legacy `*.php` routes and server-rendered workflows. It does not claim AI Assistant, Uploads, Maintenance Center, Knowledge administration, product Reports, full-resource APIs, Donate, Community v2, mail delivery, CSV export, an alternate URL scheme, or production hosting. Do not expose the local XAMPP installation or its database tools to the public internet.

## Verified-Core Scope

- Public Knowledge includes published articles, glossary entries, and error-code content; it does not include Knowledge administration.
- Authenticated users receive personal Dashboard progress, favorites, ratings, and activity. Editors and administrators receive six bounded operational KPIs and two charts; only administrators receive user identities and audit details. Dashboard is not a Reports feature.
- Community uses the active `community_posts`, `community_comments`, and `community_likes` model. The separate question/answer model is not active.
- `search_suggestions.php` and `search_event.php` are the only dedicated Search JSON endpoints. They use bounded JSON envelopes, stable error statuses, privacy filtering, and file-backed rate limits; they are not a full-resource API.
- Diagnostics is available from both navigation implementations. Reaching an outcome records completion; back and restart clear completion as the session is recomputed.

## Supported Local Environment

- Windows 10 or Windows 11
- XAMPP with PHP 8.2.x, Apache 2.4.x, and MariaDB 10.4.x
- Git
- Composer 2.10 or later
- A local checkout at `C:\xampp\htdocs\GuideMyPC`

PHP must have these extensions enabled in `C:\xampp\php\php.ini`:

- `mysqli`
- `mbstring`
- `openssl`
- `fileinfo`
- `json`
- `curl`

Composer manages the project autoloader. Install only from the committed `composer.lock`; do not commit `vendor/`.

## Quick Start

1. Install XAMPP with Apache, MySQL/MariaDB, and PHP 8.2 enabled.
2. Clone the repository into `C:\xampp\htdocs\GuideMyPC`.
3. Copy `.env.example` to `.env` and keep `.env` private.
4. Install the locked autoloader:

   ```powershell
   composer install --no-interaction
   ```

5. Configure the required `guidemypc.test` virtual host below so Apache exposes
   only `C:/xampp/htdocs/GuideMyPC/public`, then add the local hosts-file entry.
6. In the XAMPP Control Panel, start or restart Apache and start MySQL.
7. Run the setup check:

   ```powershell
   C:\xampp\php\php.exe scripts\check-local-setup.php
   ```

8. Create the schema and, optionally, safe sample content:

    ```powershell
    C:\xampp\php\php.exe database\migrate.php
    C:\xampp\php\php.exe database\seed.php
    ```

9. Open [http://guidemypc.test/](http://guidemypc.test/).

`config.php` loads database settings from the untracked `.env` file. Keep that file local and use the setup checker before troubleshooting application pages.

## Database Setup

The repository includes a versioned migration runner and safe sample-content seed. A new installation uses:

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

## Required Local Virtual Host

The release boundary exposes only `public/`; serving the repository root is not
a supported final setup. Add this virtual host to an Apache configuration file
enabled by XAMPP:

```apache
<VirtualHost *:80>
    ServerName guidemypc.test
    DocumentRoot "C:/xampp/htdocs/GuideMyPC/public"

    <Directory "C:/xampp/htdocs/GuideMyPC/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 guidemypc.test` to the Windows hosts file, restart Apache, and
keep `APP_URL=http://guidemypc.test` in `.env`. Legacy `*.php` paths are
dispatched by `public/index.php`. This is local validation only, not a
production-hosting claim. Run `scripts/verify-public-root.ps1` to validate the
same boundary in an isolated temporary Apache process.

## Local File Access and Mail

- Keep the checkout readable by the Windows user running Apache; do not grant broad write access such as `Everyone:Full Control`.
- Do not use public writable directories for logs, backups, or files. Runtime-writable paths belong in private storage outside the repository and web root. No Uploads feature is included in this release.
- XAMPP does not configure outbound mail safely by default. The password-reset request remains non-enumerating, but outbound delivery is unproven and is not a release capability.

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
| Browser shows the wrong site | Checkout location or virtual host | Confirm the folder is `C:\xampp\htdocs\GuideMyPC`, the required virtual host exposes only `public/`, and `http://guidemypc.test/` resolves locally. |

## Validation

Run the dependency-free helper checks after changing shared application code:

```powershell
C:\xampp\php\php.exe tests\helpers_test.php
```

Feature-specific validation commands and the bounded response conventions for explicitly documented JSON routes are in [`docs/application-conventions.md`](docs/application-conventions.md). The focused integration suites include `tests/diagnostic_integration_test.php` and `tests/search_endpoint_test.php`.

Before packaging, run `composer run audit:cleanup` and
`powershell -File scripts/verify-public-root.ps1`. Browser release checks use
`scripts/check-mobile-layout.js` and `scripts/check-browser-accessibility.js`
against a local Chrome DevTools endpoint. The strict source packager requires a
release commit, reviewed Visual Paradigm source and four UML exports, and 8-10
reviewed screenshots; validate its ZIP with
`scripts/verify-source-package.ps1 -PackagePath <source.zip> -Database
<disposable_name_test> -ExpectedCommit <40-character-release-sha>`.

## Release Documentation

- [`docs/release-checklist.md`](docs/release-checklist.md): verified-core release gates and sign-off boundaries
- [`docs/route-contracts.md`](docs/route-contracts.md): canonical legacy routes, approved retirements, and response contracts
- [`docs/route-inventory.md`](docs/route-inventory.md): route methods, inputs, side effects, callers, and test coverage
- [`docs/search.md`](docs/search.md): Search behavior and the two narrow JSON endpoints
- [`docs/project-structure.md`](docs/project-structure.md): current and transitional application boundaries
