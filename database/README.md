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

Run the fast gate without a database, then run the complete release gate only after the isolated database has been migrated and seeded:

```powershell
composer run verify:fast
composer run verify
```

`composer run verify` discovers every `tests/*_test.php` file in sorted order and forwards an optional `--database=<name>` argument to each test. It fails when no matching tests are present or any test fails. It does not bypass the test bootstrap: `DB_TEST_NAME` must still be explicitly configured, end in `_test`, and differ from `DB_NAME` before any integration-test connection is opened.

## Adding Migrations

Create a new file in `database/migrations/` with the next zero-padded number and a lowercase underscore name, for example `004_add_search_index.sql`. Migrations run in natural filename order. Do not alter a migration that may already be installed; add a corrective migration instead.

Each feature task owns its schema change. Keep migrations small, reviewable, and compatible with the currently supported MariaDB version. Use a backup and a tested forward-fix or restoration procedure before applying a destructive change. The runner does not provide automatic down migrations because MariaDB DDL can commit implicitly.

`026_remembered_devices.sql` is a forward-only account-security migration. It creates hash-only remembered-browser records and must be applied through the normal migration runner; do not add token values to seeds, exports, fixtures, or logs.

`027_official_download_catalog.sql` is a forward-only catalog migration. It repairs only matching official records by normalized product name or complete normalized official URL, then creates missing records; it does not delete unrelated or custom downloads. `004_official_download_catalog.sql` repeats that safe repair behavior for seeded catalogs.

If a migration fails or its checksum does not match the recorded ledger, stop rather than rerunning it blindly. Follow [`recovery.md`](recovery.md) to investigate partial DDL, restore an approved backup, or rehearse a forward recovery migration.

## Local Admins

After migrating, create a local administrator only when needed:

```powershell
C:\xampp\php\php.exe scripts\create-local-admin.php --name="Local Admin" --email=admin@example.test
```

The command prompts twice for a 12-character minimum password rather than accepting one in shell history or storing it in a seed. The Windows terminal displays typed passwords, so run it only in a private local terminal. Do not put an administrator email or password in a seed file.
