# Migration Recovery Runbook

Use this runbook when `database/migrate.php` fails, reports a checksum mismatch, or the database state is uncertain. MariaDB DDL can commit before a migration file completes, so an unrecorded migration is not proof that no schema changes occurred.

## Immediate Response

1. Stop deployment and do not rerun the failed command against the same database.
2. Record the command, target database, release commit, migration filename, error output, operator, and timestamp.
3. Take a fresh backup of the affected database and private runtime data before any investigation or recovery.
4. Keep the prior application artifact available. Restore it first when the failure also affects application behavior.

## Diagnose

Use a disposable copy or a least-privilege database account where possible. Inspect the migration ledger and the target schema:

```sql
SELECT version, checksum, applied_at
FROM schema_migrations
ORDER BY version;
```

Compare the schema with a known-good backup or a freshly migrated database at the same intended release. Record every table, column, index, constraint, or data change introduced before the failure.

## Partial Migration

If the SQL file is absent from `schema_migrations` but any of its DDL changes are present:

1. Do not delete migration history or edit the historical SQL file.
2. Do not rerun the file blindly; it may fail on objects created by the partial attempt or duplicate data changes.
3. Choose one reviewed recovery path:
   - Restore the database from the approved pre-migration backup, then rerun the unchanged migration set.
   - Create and rehearse a forward recovery migration that brings the observed partial schema to the intended state without data loss.
4. Test the chosen path first on a disposable copy of the failed database.
5. Run the complete migration command once, then confirm the ledger and schema match the intended release.

## Checksum Mismatch

A checksum mismatch means an applied historical migration file changed. Do not alter the ledger checksum or edit the installed migration to make the runner continue.

1. Restore the historical file from the release that originally applied it.
2. Add a new forward migration for the required correction.
3. Rehearse the upgrade from a representative existing schema and a fresh installation.
4. Record the original checksum, corrected migration filename, and validation evidence.

## Validation and Sign-Off

After recovery:

1. Run `database/migrate.php` a second time and confirm it applies zero files.
2. Run `database/seed.php` twice against an isolated database and compare approved row counts.
3. Run `composer run verify` against the explicitly configured `DB_TEST_NAME` database.
4. Run the Apache route and private-path smoke matrix for the deployed artifact.
5. Record backup/restore evidence, residual risk, and the release decision in `docs/release-checklist.md`.

Do not mark task `009` complete until a representative partial-failure rehearsal and backup restoration have evidence.
