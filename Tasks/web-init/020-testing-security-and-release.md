# Task: Testing, Security, and MVP Release

- Status: Not started
- Priority: Critical
- Release: R4
- Dependencies: `001-xampp-local-setup.md` through `019-accessibility-seo-and-performance.md`

## Objective

Create an automated and manual release gate proving that the MVP's core behavior, permissions, safety controls, and operational requirements work together.

## Current State

There is no automated test suite, CI configuration, static analysis, code style enforcement, release checklist, dependency audit, browser matrix, backup test, or security review process.

## Scope

- Add unit tests for validation, diagnostics, confidence scoring, search normalization, and safety policy logic.
- Add database integration tests for migrations, authorization, transactions, publication states, and ownership.
- Add browser smoke tests for homepage search, guides, accounts, diagnostics, AI shell, uploads, downloads, community, and admin.
- Add PHP syntax, code-style, static-analysis, dependency-audit, migration, accessibility, and link checks.
- Build a security test matrix for CSRF, XSS, SQL injection, authorization, sessions, rate limits, uploads, SSRF, prompt injection, and sensitive-data leakage.
- Define supported browsers and responsive devices.
- Create data backup/restore, outage, degraded-provider, and rollback checks.
- Produce a release checklist and known-risk register.

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
- scripts for lint, migration, static analysis, browser, accessibility, and link checks

## Acceptance Criteria

- [ ] One documented command runs the local fast verification suite.
- [ ] CI runs on proposed changes and produces actionable failures.
- [ ] Critical permissions and cross-user boundaries have automated coverage.
- [ ] Diagnostic graph/scoring, search, uploads, download checking, and AI safety have focused tests.
- [ ] Backup restoration and migration from the current schema have been exercised.
- [ ] No unresolved critical/high security, data-loss, or accessibility defect remains.
- [ ] Product, content safety, security, accessibility, and operations release checks are signed off.

## Validation

- Run the entire suite from a clean checkout and blank test database.
- Deliberately introduce representative syntax, migration, authorization, and accessibility failures to confirm CI catches them.
- Execute manual guest, user, moderator/editor, and admin journeys.
- Restore a backup into an isolated database and run smoke tests.

## Definition of Done

The release candidate passes repeatable automated and manual gates, and any accepted residual risk is explicit, owned, and noncritical.
