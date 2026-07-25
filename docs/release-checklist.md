# MVP Release Checklist

Record the exact commit, environment, tester, date, expected result, actual result, evidence reference, and status for every manual acceptance journey. Do not attach secrets, private uploads, account data, or raw diagnostic answers.

## Fast Gate

Run the fast gate from the repository root:

```powershell
composer run verify:fast
```

Before a release, migrate and seed the explicitly configured isolated `DB_TEST_NAME` database, then run the complete gate:

```powershell
composer run verify
```

The complete gate discovers all `tests/*_test.php` files, including `diagnostic_integration_test.php` and `search_endpoint_test.php`, and fails rather than omitting an undiscovered test. It refuses normal database names through `tests/bootstrap.php`; do not point it at `DB_NAME`. Also run `git diff --check`, the documented migration command against an isolated database, browser smoke journeys, accessibility checks, internal-link checks, and a local backup/restore drill.

## Sign-off

- Product: public Guides and Knowledge, approved Downloads, Search, accounts/progress, Diagnostics, Dashboard KPIs/charts, and canonical legacy Community flows reviewed.
- Security: CSRF, authorization, trusted-URL/SSRF controls, bounded Search JSON errors, telemetry privacy, private runtime paths, and rate limits reviewed; retired or excluded feature entry points remain absent.
- Accessibility: keyboard, focus, form errors, status messages, zoom/reflow, and media alternatives reviewed.
- Content safety: published guides, sources, downloads, diagnostics, and warnings reviewed.
- Operations: local backup/restore, migration, private storage, logs, source-package inspection, and local public-root checks reviewed. This sign-off does not claim production hosting.

## Known Risks

Historical or dormant foundations do not expand the verified release. AI Assistant, Uploads, Maintenance Center, Knowledge administration, product Reports, full-resource APIs, Donate, Community v2, outbound mail delivery, CSV export, alternate URL schemes, and production hosting remain excluded. Every residual verified-core risk must have an owner, severity, mitigation, and target commit before release.
