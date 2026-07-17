# Task: Database, Tests, and Deployment Hardening

- Status: Not started
- Priority: Critical
- Release: M6
- Dependencies: `008-public-document-root-and-routing.md`

## Objective

Turn the migrated structure into a repeatable release by hardening database upgrades, seed repeatability, test isolation, automated route coverage, packaging, deployment, backup, and rollback procedures.

## Current State

The project uses ordered checksummed SQL migrations and custom CLI integration tests. Some tests skip when seed data is absent, test database naming is inconsistent, and account cleanup can leave orphan data. MariaDB DDL can partially apply before a migration version is recorded. Source packaging currently excludes `vendor/`, and no automated HTTP route matrix proves public-root behavior.

## Scope

- Preserve ordered migration filenames and checksums.
- Add safe detection, documentation, and recovery for partially applied migrations.
- Make seeds genuinely repeatable.
- Require explicit isolated test database provisioning and reject unsafe database targets.
- Replace successful skips with deterministic fixtures or explicit failures.
- Add route-contract, private-path, authorization, publication, view, and bootstrap tests.
- Consolidate lint, Composer validation, migration, test, and HTTP smoke checks into documented commands.
- Verify the selected production Composer and packaging workflow.
- Document backup, restore, upgrade, failed-upgrade recovery, deployment, and rollback.
- Add CI if it can run the required PHP and MariaDB environment reliably.

## Non-Goals

- Requiring 100 percent line coverage
- Replacing all tests with PHPUnit in one step
- Introducing an ORM or a new database engine
- Editing historical migration files
- Claiming automatic rollback for MariaDB DDL
- Expanding product scope

## Implementation Steps

1. Add a hard guard that requires an explicitly named test database and rejects configured development/production databases.
2. Make every integration test provision required fixtures and clean all created rows.
3. Correct seed uniqueness or lookup behavior through new migrations and seed logic.
4. Add migration preflight and partial-application recovery instructions.
5. Test fresh migration, representative upgrade, second run, checksum mismatch, failed statement, and repeated seed behavior.
6. Add HTTP contract tests for every legacy route and security outcome appropriate to that route.
7. Add private-path denial, static-without-database, bootstrap, and public-root tests.
8. Create one fast local command and one complete release command.
9. Update source/deploy packaging and verify dependencies in a clean extraction.
10. Exercise backup restoration and deployment rollback with recorded evidence.

## Database Changes

Use new forward migrations for missing uniqueness, foreign-key, type, or integrity corrections approved by the relevant feature task. Every change must account for existing data and support both fresh installation and representative upgrade. Historical SQL files and recorded checksums remain immutable.

## Security and Privacy

- Tests must use fake users, tokens, content, and credentials.
- Automated commands must refuse production-like database targets.
- Deployment artifacts must exclude `.env`, logs, sessions, caches, private uploads, test evidence containing secrets, and development-only dependencies where applicable.
- Backup and restore evidence must not contain real private data.
- Dependency installation must use locked versions and documented integrity/audit checks.

## Accessibility

The HTTP suite must retain representative keyboard, landmark, validation, error, and 320px checks. Structural migration is not complete if pages render but critical workflows become inaccessible.

## Affected Files

- migration runner and new corrective migrations
- seed files and seed runner
- test bootstrap, fixtures, and test suites
- verification and release scripts
- Composer scripts and optional CI configuration
- packaging scripts
- database, deployment, backup, recovery, and release documentation
- route-contract evidence

## Rollback Strategy

Application releases must support restoring the prior artifact and configuration. Database changes require backup-first forward recovery procedures because DDL rollback may not be transactional. Document the compatibility window between old code and new schema before each release.

## Acceptance Criteria

- [ ] Tests cannot connect to a normal development or production database accidentally.
- [ ] Core integration tests never pass solely because required seed data is missing.
- [ ] Test-created users, tokens, activity, progress, diagnostics, and related rows are cleaned reliably.
- [ ] Fresh migration and representative existing-schema upgrade both succeed.
- [ ] A second migration run applies zero files.
- [ ] Repeated seeding leaves the approved rows and counts unchanged.
- [ ] Partial migration failure and checksum mismatch have tested recovery procedures.
- [ ] One fast command and one complete release command are documented and reproducible.
- [ ] Every legacy route has automated or explicitly recorded manual contract coverage.
- [ ] A clean deployment artifact can install/start with the documented Composer strategy.
- [ ] Backup restoration and application rollback have evidence.

## Validation

- Create a blank database, migrate, seed twice, run tests, and compare expected row counts.
- Upgrade a representative pre-migration schema and run the complete route matrix.
- Simulate a migration failure in an isolated disposable database and follow the recovery procedure.
- Deliberately point tests at a protected database name and confirm hard failure before any write.
- Build/extract the release artifact in a clean temporary location and run setup, migration, and verification.
- Restore a sanitized backup and run smoke tests.
- Run dependency audit, Composer validation, PHP lint, fast tests, full HTTP tests, accessibility checks, and private-path probes.

## Definition of Done

The migrated application can be installed, upgraded, tested, packaged, deployed, restored, and rolled back through documented repeatable procedures without risking normal data or depending on skipped verification.
