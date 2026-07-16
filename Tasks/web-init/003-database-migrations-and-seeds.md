# Task: Database Migrations and Seeds

- Status: Not started
- Priority: Critical
- Release: R0
- Dependencies: `002-security-bootstrap.md`

## Objective

Replace the mutable phpMyAdmin dump with a versioned, repeatable schema process and sanitized development content that supports the MVP roadmap.

## Current State

The removed legacy prototype dump combined schema, sample content, and a seeded admin account. It contained 11 tables, duplicated guide content, and no migration history. Reconstruct a clean baseline from the existing application schema and previous repository history; do not restore the dump to the public web root. Several planned features have no data model.

## Scope

- Select and document a numbered SQL migration convention.
- Create an idempotent migration runner and migration-history table.
- Convert the existing schema to an initial migration without losing valid local data.
- Separate optional development seeds from schema migrations.
- Remove seeded personal identities and password hashes from tracked files.
- Add indexes, constraints, timestamps, publication states, and consistent naming needed by R1-R4.
- Add schema changes in feature-owned migrations rather than one speculative mega-schema.
- Document backup and rollback expectations for destructive migrations.

## Non-Goals

- Switching to PostgreSQL
- Introducing an ORM
- Production data migration before a production environment exists
- Seeding real community or user data

## Implementation Steps

1. Add `database/migrations/` and `database/seeds/` conventions.
2. Create a migration-history table and CLI-only runner.
3. Translate the current schema into a clean baseline.
4. Resolve duplicate guide body versus guide-step ownership as part of task `009`.
5. Add missing uniqueness and range constraints, including rating bounds and ordered guide steps.
6. Provide safe categories, guides, and downloads as optional sample content.
7. Provide a documented command to create an admin locally rather than shipping one.

## Database Changes

- Baseline existing tables and foreign keys.
- Add `schema_migrations`.
- Add feature tables only through migrations attached to tasks `007`-`018`.
- Standardize `created_at` and `updated_at` where records are editable.

## Security and Privacy

- Migration and seed scripts must be CLI-only or admin-protected and disabled in production HTTP routes.
- Never include real email addresses, credentials, access tokens, private logs, or screenshots in seeds.
- Back up data before destructive changes.

## Accessibility

Not applicable to schema execution. Any web-facing migration error must use the generic application error page.

## Affected Files

- new `database/migrations/` files
- new `database/seeds/` files
- migration runner and setup documentation

## Acceptance Criteria

- [ ] A blank database can be fully migrated in one documented command.
- [ ] Re-running migrations performs no duplicate changes.
- [ ] Existing prototype data can be upgraded without manual table editing.
- [ ] Seeds contain no personal data or reusable admin password.
- [ ] Foreign keys, uniqueness, ranges, and search indexes are documented and tested.
- [ ] Migration history identifies the exact installed schema version.

## Validation

- Migrate a blank database, run seeds, and exercise existing pages.
- Run the migration command a second time.
- Upgrade a copy of the current schema and compare row counts for existing core tables.
- Search tracked database files for emails, password hashes, and secret-like values.

## Definition of Done

Schema state is reproducible, versioned, safe to share, and ready to evolve one feature at a time.
