# Task: Testing, Security, and MVP Release

- Status: Foundation implemented
- Priority: Critical
- Release: R4
- Dependencies: `001-xampp-local-setup.md` through `019-accessibility-seo-and-performance.md`

## Objective

Create an automated and manual release gate proving that the MVP's core behavior, permissions, safety controls, and operational requirements work together.

## Current State

Fast PHP integration tests, PHP syntax checks, local setup validation, a release checklist, and representative HTTP/security smoke checks exist. CI, browser automation, static analysis, full backup restoration, and a complete evidence matrix remain incomplete.

## Scope

- Add unit tests for validation, diagnostics, confidence scoring, search normalization, and safety policy logic.
- Add database integration tests for migrations, authorization, transactions, publication states, and ownership.
- Add browser smoke tests for homepage search, guides, accounts, diagnostics, AI shell, uploads, downloads, community, and admin.
- Add PHP syntax, code-style, static-analysis, dependency-audit, migration, accessibility, and link checks.
- Build a security test matrix for CSRF, XSS, SQL injection, authorization, sessions, rate limits, uploads, SSRF, prompt injection, and sensitive-data leakage.
- Define supported browsers and responsive devices.
- Create data backup/restore, outage, degraded-provider, and rollback checks.
- Produce a release checklist and known-risk register.
- Record manual acceptance evidence with build/commit, environment, tester, date, steps, expected result, actual result, status, and evidence reference.
- Reconcile every third-party runtime, development, browser, CDN, font, icon, test, and documentation dependency against lockfiles and source references.
- Verify GitHub checks, reviews, issues, and release evidence refer to the exact release/submission commit.
- Run submission preflight against a fresh extraction of the clean source archive defined by task `022`.
- Record the named end-to-end scenarios required for the final academic evidence set.

## Non-Goals

- Claiming complete security from automated scanners
- Requiring 100% line coverage
- Testing third-party providers beyond contract and failure simulations
- Launching before critical findings are resolved

## Implementation Steps

1. Select lightweight PHP test, browser, static-analysis, and formatting tools compatible with PHP 8.2.
2. Make test database creation and migration isolated and repeatable.
3. Prioritize high-risk business and security behavior over snapshot-heavy UI tests.
4. Add CI stages that fail on syntax, tests, migration, security audit, or critical accessibility errors.
5. Create manual exploratory charters for beginner comprehension and support safety.
6. Run a release candidate through the full matrix and document defects/risks.
7. Require sign-off for product, security, accessibility, content safety, and operations.
8. Save manual test evidence in a consistent format and link screenshots/logs without including secrets or private user data.
9. Compare the dependency inventory with Composer metadata, vendored files, CDN references, fonts/icons, test tools, and diagram/export tools.
10. Extract the proposed submission archive into a clean temporary directory and repeat setup, migration, and fast verification from its tracked documentation.

## Database Changes

No product changes. Tests use an isolated database and must never modify developer or production data.

## Security and Privacy

Use fake users, uploads, API responses, and secrets in tests. Do not send automated test content to live AI providers unless explicitly configured and isolated. Treat all critical/high security failures as release blockers.

## Accessibility

Automated and manual accessibility checks are part of the release gate, including keyboard and screen-reader coverage of critical journeys.

## Affected Files

- new test directories and fixtures
- Composer development configuration
- CI workflow/configuration
- release checklist and risk register
- manual acceptance evidence and dependency inventory
- scripts for lint, migration, static analysis, browser, accessibility, and link checks

## Acceptance Criteria

- [ ] One documented command runs the local fast verification suite.
- [ ] CI runs on proposed changes and produces actionable failures.
- [ ] Critical permissions and cross-user boundaries have automated coverage.
- [ ] Diagnostic graph/scoring, search, uploads, download checking, and AI safety have focused tests.
- [ ] Backup restoration and migration from the current schema have been exercised.
- [ ] No unresolved critical/high security, data-loss, or accessibility defect remains.
- [ ] Product, content safety, security, accessibility, and operations release checks are signed off.
- [ ] Manual evidence identifies the exact tested commit, environment, tester, expected outcome, actual outcome, and status.
- [ ] Every included dependency is documented with exact version, purpose, canonical source, and license; no undocumented dependency remains.
- [ ] GitHub and review evidence corresponds to the release commit and does not claim work that did not occur.
- [ ] A fresh extraction of the submission archive can be configured, migrated, and verified using only included documentation and declared dependencies.
- [ ] Registration, login, logout, password reset request/reset, and account-history behavior are verified.
- [ ] One administrator content workflow proves create, read, update, delete, validation feedback, and role denial (`403`).
- [ ] Search/filter/sort/pagination are proven with more than 25 realistic records where the selected entity supports pagination.
- [ ] Diagnostic completion is tested with JavaScript disabled, invalid/tampered transitions rejected, and uncertainty displayed when evidence is insufficient.
- [ ] Sensitive paths return `403`, UTF-8 content renders correctly, and a controlled error/404 state is recorded.
- [ ] Desktop and 320px mobile behavior are checked in the declared browser matrix.

## Validation

- Run the entire suite from a clean checkout and blank test database.
- Deliberately introduce representative syntax, migration, authorization, and accessibility failures to confirm CI catches them.
- Execute manual guest, user, moderator/editor, and admin journeys.
- Restore a backup into an isolated database and run smoke tests.
- Compare dependency inventory records against the release tree and network-loaded assets.
- Run the complete submission preflight and secret scan against the archive, not only the working tree.

## Definition of Done

The release candidate passes repeatable automated and manual gates, and any accepted residual risk is explicit, owned, and noncritical.
