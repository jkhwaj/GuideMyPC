# Task: Production Deployment

- Status: Not started
- Priority: Critical
- Release: R4
- Dependencies: `020-testing-security-and-release.md`

## Objective

Deploy the tested PHP application to a hardened production environment with HTTPS, backups, monitoring, controlled releases, and rollback. XAMPP must remain local-development tooling only.

## Current State

The repository has no production environment definition, deployment process, secret management, HTTPS configuration, scheduled jobs, backup policy, monitoring, health checks, log rotation, incident process, or rollback instructions.

## Scope

- Select a supported production PHP host with managed or hardened MariaDB and documented capacity.
- Configure a public document root that excludes configuration, migrations, tasks, logs, private uploads, and source-control metadata.
- Add HTTPS, secure cookies, production security headers, disabled error display, and least-privilege database credentials.
- Store secrets in the host's secret/environment manager.
- Define build/release, maintenance mode, migration, smoke-test, and rollback steps.
- Configure scheduled jobs for cleanup, link checks, review reminders, and other task-owned maintenance.
- Add Sentry or equivalent error monitoring, uptime checks, structured logs, alert ownership, and privacy-friendly analytics.
- Add encrypted database/file backups, retention, off-site storage, and restoration drills.
- Document incident response and provider outage degradation for AI, mail, storage, and analytics.
- Maintain a named, versioned production checklist that records the release commit, migration version, operator, timestamps, outcome, and rollback decision.

## Non-Goals

- Running public production from a developer's XAMPP installation
- Premature multi-region architecture
- 99.9% claims before monitoring data exists
- Zero-downtime complexity unsupported by product usage

## Implementation Steps

1. Document the selected host, PHP extensions, database version, storage, mail, and scheduled-job capabilities.
2. Provision staging with production-equivalent security and sanitized data.
3. Configure secrets, web root, HTTPS, headers, logs, uploads, database, and outbound provider restrictions.
4. Automate or strictly document release artifact, maintenance, migration, cache, smoke-test, and rollback steps.
5. Configure monitoring, uptime, alerting, dashboards, and retention.
6. Configure and test backup/restore for database and private files.
7. Deploy a release candidate to staging, complete task `020`, then use the same process for production.
8. Complete and retain the versioned production checklist for staging rehearsal and the production release.

## Database Changes

No feature schema. Production uses versioned migrations with pre-migration backups and records the deployed application/migration version.

## Security and Privacy

- Use least-privilege service accounts, secret rotation, HTTPS-only cookies, restricted outbound access, and private backups.
- Protect monitoring and logs from sensitive conversation/upload contents.
- Document subprocessors and retention for AI, storage, email, error monitoring, and analytics.
- Define security contact and incident-notification responsibilities.

## Accessibility

Maintenance and outage pages must be accessible and provide status/recovery information without relying on scripts or visual-only indicators.

## Affected Files

- production/staging environment documentation
- deployment and scheduled-job scripts/configuration
- server/web-root configuration
- monitoring, backup, health-check, and incident runbooks
- privacy/subprocessor documentation
- versioned production checklist covering deployment, migration, smoke test, rollback, backup restoration, monitoring, scheduled jobs, private paths, and debug-utility removal

## Acceptance Criteria

- [ ] XAMPP is not used to host production.
- [ ] Only intended public assets/routes are reachable from the production web root.
- [ ] HTTPS, secure cookies, headers, secret storage, and production error handling are verified.
- [ ] Deployment and rollback have both been rehearsed in staging.
- [ ] Database and private-file backups are encrypted, monitored, and successfully restored in a drill.
- [ ] Health, error, uptime, job, disk, and backup failures alert an assigned owner.
- [ ] AI/provider outages degrade to guides, search, diagnostics, or community rather than breaking the site.
- [ ] The release version and migration version are observable.
- [ ] A signed/versioned production checklist records deployment, migration, smoke test, rollback readiness, backup restoration, monitoring, scheduled jobs, private-path checks, and absence of public debug utilities.

## Validation

- Run external TLS/header checks and verify blocked private paths.
- Perform staging deployment, migration, smoke test, rollback, and redeployment.
- Restore production-like backups into an isolated environment.
- Simulate AI, mail, storage, database, and scheduled-job failures.
- Review logs and monitoring for sensitive-data leakage.
- Compare the completed checklist with the deployed release commit and migration history.

## Definition of Done

The MVP is deployed through a repeatable, reversible, monitored process with tested backups and clear operational ownership.
