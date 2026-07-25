# Phase 3 Verified-Core Hardening Evidence

Date: 2026-07-22
Branch: `final-project-submission-readiness`
Baseline commit: `8dd43bcb8ef465e01f99756e48d4942ef6132059`

This is worktree evidence, not final-release sign-off. Live Apache, fresh-install,
backup/restore, accessibility, screenshot, UML, DOCX, strict package, and clean
extraction gates remain open.

## Runtime Hardening

### Diagnostics

- Both navigation implementations link directly to the seeded
  `diagnostic.php?flow=pc-no-power` entry point.
- `diagnostic.php` now rejects non-GET requests through the shared method guard.
- Diagnostic answer, back, and restart writes run through a testable transition
  boundary. Dependent writes are transactional.
- Reaching an outcome sets `diagnostic_sessions.completed_at`; back and restart
  clear completion while recomputing the current node.
- Invalid actions and tampered options return a non-mutating invalid result for
  the route to map to its bounded `422` error.
- `tests/diagnostic_integration_test.php` verifies guest ownership, a 48-character
  public ID, invalid-option non-mutation, one-node advancement, outcome
  completion, answer counts, back, restart, invalid action, and expiry.

Rollback: restore `diagnostic.php`, `diagnostic_action.php`,
`includes/diagnostics.php`, both navbar files, and the prior documentation as one
group. Remove the integration test only with that rollback. No migration change
is involved.

### Narrow Search JSON Endpoints

- `search_suggestions.php` and `search_event.php` explicitly identify
  themselves as JSON endpoints before bootstrap, so method and rate-limit errors
  use the same bounded JSON envelope even without an `Accept` header.
- Search-event recording now returns a boolean. The endpoint reports
  `recorded: false` when privacy filtering or type/state validation discards an
  event instead of claiming a write occurred.
- `tests/search_endpoint_test.php` invokes the real endpoint scripts through the
  CLI-only `tests/search_endpoint_probe.php` against the isolated test database
  and temporary external rate-limit storage.
- Coverage includes success/error envelope separation, 24-character request
  IDs, method rejection, an eight-item suggestion bound, privacy filtering,
  one stored aggregate event, truthful status, and both rate-limit boundaries.

Rollback: restore `includes/functions.php`, `includes/search.php`, both endpoint
scripts, and their contract documentation together; remove both endpoint test
files only with that rollback.

## Retained Surface And Claims

- Static-page rendering tests require both current and legacy navigation to
  expose Diagnostics and reject AI/Donate links.
- Sitemap integration requires Contact and rejects AI, Donate, and a Reports
  path.
- Root README, current technical documentation, historical task plans, report
  plan, UML scope, screenshot plan, third-party inventory, test evidence, and
  team source now distinguish verified core from explicit exclusions.
- Historical task bodies and dated implementation evidence were preserved.
  Scope notices and the authoritative final-project addendum prevent old plans
  from expanding the release.
- Tracked team-document PII was replaced with private-DOCX placeholders.
- Submission sources continue to mark screenshots, four UML diagrams and
  `.vpp`, Word documents, live validation, and final packaging as pending.

Rollback: documentation can be restored independently only if the replacement
still records the owner-approved exclusions and does not reintroduce PII or
unsupported claims.

## Validation

| Command | Result |
| --- | --- |
| `php tests/diagnostic_integration_test.php --database=guidemypc_readiness_test` | Pass |
| `php tests/search_endpoint_test.php --database=guidemypc_readiness_test` | Pass |
| `composer run verify:fast` | Pass: strict Composer validation, 147 current PHP source files linted, helper tests passed |
| `php scripts/verify.php --database=guidemypc_readiness_test` | Pass: 16 test files, 0 failures |
| `php tests/route_map_test.php` | Pass: exact retained maps and safe retired-route 404 probes |
| `php tests/helpers_test.php` | Pass: retained pages/navigation and helper policies |
| `git diff --check` | Pass; line-ending conversion warnings only |
| `git diff --name-only -- database/migrations` | No output; historical migrations unchanged |

The incremental graph update re-parsed 72 changed tracked files. Change
detection reported risk `0.60` because new untracked Phase 3 tests are not in
the tracked-file graph snapshot; the direct targeted tests and 16-file full
suite above cover the named Diagnostic and Search changes.
