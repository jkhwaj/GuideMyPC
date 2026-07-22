# Phase 2 Safe Cleanup Evidence

## Scope And Authority

- Date: 2026-07-22.
- Baseline commit: `8dd43bcb8ef465e01f99756e48d4942ef6132059`.
- Branch: `final-project-submission-readiness`.
- Authority: the owner-approved verified-core scope in
  [`readiness-baseline.md`](readiness-baseline.md), after the complete caller and
  rollback inventory in [`file-inventory.md`](file-inventory.md).
- No file was moved or merged. No historical migration was edited, renamed,
  reordered, moved or deleted.

## Executed Groups

| Group | Executed change | Preserved boundary | Rollback |
| --- | --- | --- | --- |
| Local tooling | Removed tracked machine-bound `opencode.json`; added it to `.gitignore`. | `AGENTS.md` and application runtime are unchanged. | Restore the config only as a local untracked file, or restore the tracked file and ignore rule together if repository policy changes. Restart OpenCode after recreating a local project config because config is loaded only at startup. |
| AI | Removed the wrapper, `PageController::ai()`, view, unused helper, legacy metadata, crawler rule, route-map entry and active-page tests. | Diagnostics and immutable migration 017 remain. | Restore the complete wrapper/controller/view/helper/metadata/robots/map/test slice. |
| Donate | Removed both footer links, wrapper, `PageController::donate()`, view, metadata, route-map entry and active-page tests. | Contact remains. | Restore the complete link/wrapper/controller/view/metadata/map/test slice. |
| Uploads | Removed only the uncalled `includes/uploads.php` helper. | Private-storage, `.gitignore`, package exclusions and immutable migration 018 remain. | Restore the helper file; do not change historical SQL. |
| Maintenance Center | Removed only the uncalled `includes/maintenance.php` helper. | Public Knowledge maintenance content, category dependency safety and immutable migration 015 remain. | Restore the helper file; do not weaken dependency checks or change historical SQL. |
| Reports | Removed unused `Authorization::VIEW_REPORTS` and its unit expectations. | Role-aware Dashboard KPIs/charts and immutable migration 019 remain. | Restore the capability and authorization expectations together. |
| Knowledge/API scope | Removed nonexistent Knowledge-administration contracts and recorded the absence of full-resource APIs. | Public Knowledge and the two narrow Search JSON endpoints remain. | Documentation-only rollback requires owner approval plus an implemented and tested feature. |

## Route Evidence

`tests/route_map_test.php` now verifies the exact 53-route map, confirms that
`ai.php` and `donate.php` are absent from every route map and from the repository
root, and invokes the real `public/index.php` through
`tests/retired_route_probe.php`. Both retired paths produce:

- HTTP status `404` as observed through `http_response_code()` at shutdown;
- title `Page not found | GuideMyPC`;
- bounded message `The requested page was not found.`;
- no redirect and no retired feature content.

Targeted runtime reference scanning after removal found the retired route names
only in the two test files that enforce this contract. Documentation keeps only
retirement/history references or remains scheduled for Phase 3 claim cleanup.

## Validation Results

| Command/check | Result |
| --- | --- |
| `php tests/route_map_test.php` | PASS; exact maps and both real front-controller 404 responses. |
| `php tests/authorization_test.php` | PASS; capability matrix without Reports. |
| `php tests/helpers_test.php` | PASS; retained pages and helpers render/behave correctly. |
| `composer run verify:fast` | PASS; strict Composer validation, 144 current PHP source files linted, helper tests passed. |
| `php scripts/verify.php --database=guidemypc_readiness_test` | PASS; 14 test files, 0 failures. |
| Runtime reference scan for retired routes/methods/capability/Knowledge-admin names | PASS; only the explicit retired-route test fixtures remain outside Markdown evidence/history. |
| `git diff --name-only -- database/migrations` | PASS; no output. |
| `git diff --check` | PASS; line-ending conversion warnings only, no whitespace errors. |
| `git check-ignore -v --no-index opencode.json` | PASS; local project config is ignored by `.gitignore`. |
| Generic secret/runtime ignore probes | PASS for `.env`, `vendor/`, logs, uploads, storage and database backups. |
| Incremental code-review graph + change detection | PASS; 22 baseline files updated, no affected execution flow found. Reported structural test gaps are covered by direct helper, authorization and route-map tests that the graph does not map to classes. |

## Deferred Gates

This evidence does not claim completion of live Apache private-path probes,
responsive/manual review, documentation/UML/screenshots, package generation,
clean extraction or restore rehearsal. Those remain Phase 3-6 gates.
