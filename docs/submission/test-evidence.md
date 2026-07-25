# Readiness Test Evidence

This record contains Phase 2 safe-cleanup results from `cleanup-evidence.md`
and Phase 3 results from `hardening-evidence.md`. The readiness
work is based on commit `8dd43bcb8ef465e01f99756e48d4942ef6132059` on branch
`final-project-submission-readiness`; it does not identify a final release
commit and is not final-release sign-off.

## Phase 2 Targeted Results

| Date | Environment | Check | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- |
| 2026-07-22 | Windows/XAMPP, PHP 8.2.12 | `php tests/route_map_test.php` | Exact 53-route map passed; retired `ai.php` and `donate.php` produced the standard bounded 404 through the real front controller | Pass | `cleanup-evidence.md` |
| 2026-07-22 | Windows/XAMPP, PHP 8.2.12 | `php tests/authorization_test.php` | Capability matrix passed without product Reports | Pass | `cleanup-evidence.md` |
| 2026-07-22 | Windows/XAMPP, PHP 8.2.12 | `php tests/helpers_test.php` | Retained pages and helper behavior passed | Pass | `cleanup-evidence.md` |
| 2026-07-22 | Windows/XAMPP, PHP 8.2.12 | `composer run verify:fast` | Strict Composer validation, 144 current PHP files linted, and helper tests passed | Pass | `cleanup-evidence.md` |
| 2026-07-22 | Windows/XAMPP, PHP 8.2.12, MariaDB 10.4.32 | `php scripts/verify.php --database=guidemypc_readiness_test` | 14 test files completed with zero failures | Pass | `cleanup-evidence.md` |
| 2026-07-22 | Readiness worktree | Retired-feature reference scan and migration-diff check | Only explicit retired-route test fixtures remained outside Markdown evidence/history; no migration diff | Pass | `cleanup-evidence.md` |

These results verify the Phase 2 cleanup at the recorded readiness state. They
must not be presented as a final live-Apache, accessibility, package,
clean-extraction, or backup/restore pass.

## Phase 3 Targeted Results

| Date | Check | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- |
| 2026-07-22 | Submission/team documentation scope and PII review | Plans describe only retained scope, keep root `*.php` URLs canonical, and tracked team source contains no real member PII | Eight allowed source files reviewed; plans use only the retained scope and the prior tracked name, ID, email, and phone values are absent from `docs/` Markdown | Pass | Current Phase 3 diff and targeted zero-match PII scan |
| 2026-07-22 | `git diff --check -- docs/submission docs/team/README.md` | No whitespace errors in allowed-path changes | No whitespace errors; Git emitted line-ending conversion warnings only | Pass | Console result after the Phase 3 edits |
| 2026-07-22 | `php tests/diagnostic_integration_test.php --database=guidemypc_readiness_test` | Ownership, transition, completion, back, restart, invalid input, and expiry behavior is deterministic | Focused Diagnostic integration test passed | Pass | `hardening-evidence.md` |
| 2026-07-22 | `php tests/search_endpoint_test.php --database=guidemypc_readiness_test` | Both narrow endpoints preserve bounded JSON methods, envelopes, privacy, storage, truthful status, and rate limits | Focused endpoint test passed | Pass | `hardening-evidence.md` |
| 2026-07-22 | `composer run verify:fast` | Composer manifest, current PHP source lint, and helper policies pass | 147 current PHP source files linted; helper tests passed | Pass | `hardening-evidence.md` |
| 2026-07-22 | `php scripts/verify.php --database=guidemypc_readiness_test` | Complete current custom suite passes | 16 test files completed with zero failures | Pass | `hardening-evidence.md` |

## Phase 4 Release-Hardening Results

| Date | Check | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- |
| 2026-07-22 | Isolated Apache with `public/` as `DocumentRoot` | Approved routes/assets work; retired, unknown, metadata, source, and private paths fail safely | Full route/asset/JSON/private-path matrix passed | Pass | `release-hardening-evidence.md` |
| 2026-07-22 | Fresh migration and repeat run on `guidemypc_release_test` | All immutable migrations apply once and none reapply | 25 applied, then 0 applied of 25 | Pass | `release-hardening-evidence.md` |
| 2026-07-22 | Seed twice and compare approved counts | Seed is idempotent and projection counts remain stable | Both runs processed 3 files and rebuilt 12 documents; six recorded counts matched | Pass | `release-hardening-evidence.md` |
| 2026-07-22 | Backup and independent restore | Backup restores ledger, schema, data, and application behavior | 25/25 checksums, 54/54 tables, key counts, zero pending migrations, and 16/16 tests matched | Pass | `release-hardening-evidence.md` |

## Remaining Final-Release Evidence

- Final full validation against the exact submitted commit and isolated fresh
  database.
- Keyboard, screen-reader/accessibility, browser, responsive, and safe
  error-state checks across retained scope.
- Strict final package generation, prohibited-content/PII review, clean
  extraction, and independent outer-package review.

Do not mark these items passed until the final commit, named tester, date,
expected/actual result, and non-sensitive evidence reference are recorded.

## Phase 5 Artifact Results

| Date | Check | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- |
| 2026-07-23 | Visual Paradigm project and exports | Native VPP contains four required diagrams and each export is readable and scope-aligned | VPP opened with Use Case, Class, Activity, and State Machine diagrams; four 1600x1000 exports visually passed | Pass | `artifact-evidence.md` |
| 2026-07-23 | Screenshot capture and redaction | Exactly 8-10 retained-scope images, at least two mobile, no sensitive evidence | Ten images passed visual review; two are 320x800; only disposable sanitized identity appears | Pass | `screenshots/README.md`, `artifact-evidence.md` |
| 2026-07-23 | Private Word artifacts | Both DOCX files open; report is Hebrew, paginated, captioned, and self-contained | Six-page Readme and 28-page report opened; report has page numbers, 14 embedded figures, and zero external relationships | Pass | `artifact-evidence.md` |
| 2026-07-23 | Ignore and tracked-PII boundary | Private/generated artifacts are ignored and supplied private values do not enter tracked source | `git check-ignore -v` passed for DOCX/VPP/screenshots; tracked-source PII scan returned zero matches | Pass | `artifact-evidence.md` |

## Phase 6 Pre-Commit Results

| Date | Check | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- |
| 2026-07-23 | Strict manifest, PHP lint, dependency-free tests, and cleanup audit | Current source is syntactically valid, route-exact, migration-immutable, and free of prohibited/private paths | Composer strict validation passed; 148 PHP files linted; helper, authorization, and 53-route tests passed; cleanup audit passed 265 files before the final browser-audit source was added | Pass | Pre-commit console evidence |
| 2026-07-23 | Fresh `guidemypc_final_precommit_test` migrate/repeat/seed/full suite | 25 migrations apply once, repeat applies zero, seed loads, all current tests pass | 25 then 0 migrations; three seeds; 12 search documents; 16 test files and 0 failures; disposable database dropped | Pass | Pre-commit console evidence |
| 2026-07-23 | Isolated Apache `public/` matrix | Approved routes/assets work and private/retired/unknown paths fail safely | Syntax and complete route/asset/JSON/private-path matrix passed | Pass | `scripts/verify-public-root.ps1` output |
| 2026-07-23 | Chrome desktop/mobile semantic, accessibility-tree, keyboard, error and exception audit | Representative retained routes have one main/h1, labels and names, non-empty accessibility trees, visible advancing focus, no duplicate IDs or browser exceptions | Seven retained/error URLs passed at 1440x900 and 320x800 after correcting the Downloads page heading | Pass | `scripts/check-browser-accessibility.js` output |
| 2026-07-23 | Chrome 320px overflow matrix | Representative retained routes and safe 404 do not overflow horizontally | Seven URLs reported 320px document width at a 320px viewport | Pass | `scripts/check-mobile-layout.js` output |

These are release-candidate pre-commit results. The exact release SHA, strict
source ZIP hash, clean-extraction database/test/Apache results, and outer
artifact review are recorded only after the authorized commit.
