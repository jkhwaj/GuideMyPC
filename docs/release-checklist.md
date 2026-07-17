# MVP Release Checklist

Record the exact commit, environment, tester, date, expected result, actual result, evidence reference, and status for every manual acceptance journey. Do not attach secrets, private uploads, account data, or raw diagnostic answers.

## Fast Gate

Run from the repository root after starting local MariaDB and applying migrations:

```powershell
C:\xampp\php\php.exe scripts\verify.php
```

Before a release, also run PHP lint, `git diff --check`, the documented migration command against an isolated database, browser smoke journeys, accessibility checks, internal-link checks, and a backup/restore drill.

## Sign-off

- Product: core guide, search, account, diagnostic, download, and community flows reviewed.
- Security: CSRF, authorization, uploads, SSRF, AI safety, and rate-limit controls reviewed.
- Accessibility: keyboard, focus, form errors, status messages, zoom/reflow, and media alternatives reviewed.
- Content safety: published guides, sources, downloads, diagnostics, and warnings reviewed.
- Operations: backup/restore, migration, rollback, private storage, logs, and deployment checklist reviewed.

## Known Risks

This prototype has several feature foundations committed ahead of their complete user-facing workflows. Do not claim an unfinished task is production-ready. Every residual risk must have an owner, severity, mitigation, and target task/commit before release.
