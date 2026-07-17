# Database Workflow

The `database/` directory contains schema-only migrations and optional, public sample content. It never contains database exports, credentials, real user data, password hashes, or a default administrator account.

## Commands

Run these commands from the repository root with XAMPP MariaDB running:

```powershell
C:\xampp\php\php.exe database\migrate.php
C:\xampp\php\php.exe database\seed.php
```

`migrate.php` creates the database named by `DB_NAME` when the local database account has permission, then records each applied SQL file and SHA-256 checksum in `schema_migrations`. Re-running it is safe: applied migrations are skipped, while a changed historical migration stops with an error.

`seed.php` is optional and idempotent. It only adds anonymous categories, a guide, steps, and official resource links. It does not add accounts, community content, credentials, or password hashes.

For an isolated local validation database, set `DB_TEST_NAME` in `.env` to a database ending in `_test` and distinct from `DB_NAME`. Tests refuse to run against any other name. Create and seed it explicitly:

```powershell
C:\xampp\php\php.exe database\migrate.php --database=guidemypc_test
C:\xampp\php\php.exe database\seed.php --database=guidemypc_test
```

The integration suite requires the seeded test database:

```powershell
C:\xampp\php\php.exe scripts\verify.php
```

## Adding Migrations

Create a new file in `database/migrations/` with the next zero-padded number and a lowercase underscore name, for example `004_add_search_index.sql`. Migrations run in natural filename order. Do not alter a migration that may already be installed; add a corrective migration instead.

Each feature task owns its schema change. Keep migrations small, reviewable, and compatible with the currently supported MariaDB version. Use a backup and a tested forward-fix or restoration procedure before applying a destructive change. The runner does not provide automatic down migrations because MariaDB DDL can commit implicitly.

## Local Admins

After migrating, create a local administrator only when needed:

```powershell
C:\xampp\php\php.exe scripts\create-local-admin.php --name="Local Admin" --email=admin@example.test
```

The command prompts twice for a 12-character minimum password rather than accepting one in shell history or storing it in a seed. The Windows terminal displays typed passwords, so run it only in a private local terminal. Do not put an administrator email or password in a seed file.
