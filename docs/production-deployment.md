# Production Deployment Runbook

XAMPP is local development tooling only. Production requires a hardened PHP 8.2 host, managed or least-privilege MariaDB, HTTPS, private object/file storage, host-managed secrets, and an Apache/Nginx public root that excludes repository source, `.env`, migrations, tasks, logs, backups, and private uploads.

## Release Steps

1. Record the release commit, operator, target environment, and migration version.
2. Confirm task 020 passed for the same commit and staging data is sanitized.
3. Create and verify encrypted database and private-file backups outside the web root.
4. Create the immutable deployment artifact with `scripts/package-deploy.ps1 -Commit <release-commit> -OutputPath <artifact.zip>`. It installs locked production dependencies into `vendor/`; `scripts/package-source.ps1` remains the clean dependency-free source archive tool.
5. Enable accessible maintenance status if needed; deploy the immutable release artifact and host-managed secrets.
6. Serve only the artifact's `public/` directory through Apache or Nginx.
7. Run migrations once with a least-privilege migration account, then run smoke checks for home, search, guides, account, diagnostics, and provider-degraded paths.
8. Verify HTTPS, secure cookies, headers, blocked private paths, logs, health checks, scheduled jobs, backup monitoring, and alert ownership.
9. Record outcome, timestamps, evidence, and rollback decision in the checklist.

## Rollback

Stop if a migration, smoke check, privacy boundary, or critical security check fails. Restore the previous application artifact first. Restore a database/private-file backup only when the migration cannot be safely forward-fixed and the recovery point has been approved. Record the incident, affected commit, migration version, operator, and recovery outcome.

## Provider Degradation

If AI, mail, analytics, storage, or monitoring fails, do not expose provider errors or retry indefinitely. Keep guides, search, diagnostics, and community available where safe; show a clear accessible fallback message; alert the assigned owner; and avoid sending queued private data until the provider is healthy.
