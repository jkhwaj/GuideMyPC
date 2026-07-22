# Phase 4 Release Hardening Evidence

Date: 2026-07-22
Branch: `final-project-submission-readiness`
Baseline commit: `8dd43bcb8ef465e01f99756e48d4942ef6132059`

This evidence belongs to the readiness worktree. It is not the final release
commit or final package sign-off.

## Public-Only Apache Boundary

`scripts/verify-public-root.ps1` generated a temporary isolated Apache 2.4.58
configuration with `public/` as `DocumentRoot`, validated it with `httpd -t`,
started it on loopback, ran the probes below, and removed its temporary
configuration and response files after shutdown.

Passed probes:

- `/`, `/guides.php`, and `/contact.php` returned expected HTML through the
  front controller and canonical legacy paths.
- `/assets/css/style.css` and the compatibility `/css/style.css` alias returned
  the expected first-party asset.
- `robots.txt` contained the local public-root sitemap URL and no retired AI
  rule.
- unknown, AI, and Donate paths returned the standard bounded safe 404.
- wrong-method requests to both narrow Search endpoints returned bounded JSON
  `405` responses with `application/json; charset=utf-8` even without an
  `Accept` header.
- `/.env` and `/.git/config` were denied with 403.
- `config.php`, Composer metadata, and paths under `app/`, `bootstrap/`,
  `database/`, `docs/`, `includes/`, `scripts/`, `Tasks/`, and `tests/` returned
  bounded 404 responses and exposed no repository path.

The source setup contract now requires `guidemypc.test` with
`C:/xampp/htdocs/GuideMyPC/public` as its local document root. `.env.example`,
README, and `public/robots.txt` use that local-only URL. This does not claim
production hosting.

Rollback: restore the prior README/environment/robots wording only together
with an equally strict public-only setup. The verification script may be
removed only if replaced by an equivalent automated Apache matrix.

## Fresh Migration And Seed Repeatability

Disposable database: `guidemypc_release_test`.

| Check | Actual result |
| --- | --- |
| Fresh `database/migrate.php` | 25 migration files applied successfully |
| Second migration run | 0 applied, 25 total |
| First seed run | 3 files processed; 12 Guide search documents rebuilt |
| Second seed run | 3 files processed; 12 Guide search documents rebuilt |
| Counts after each seed | categories 6; Guides 12; Guide steps 35; Knowledge articles 9; Diagnostic flows 1; search documents 12 |
| Full suite | 16 test files, 0 failures |

All 25 historical migration files, including both 023 files and excluded
feature schema history, remained unchanged.

## Backup And Restore Rehearsal

Source database: `guidemypc_release_test`
Independent destination: `guidemypc_restore_test`

`mysqldump` ran with single-transaction, routines, events, triggers, hex-blob,
and UTF-8 options. The SQL file was written outside the repository under the
approved temporary workspace.

- Backup size: 114,249 bytes.
- Backup SHA-256:
  `AACAD925FE97055B0F0044FEA37269022D72A030A2D9C21DEF56F8CA26CBB1C3`.
- Restore completed without client errors.
- Post-restore migration run applied 0 files and recognized all 25 migrations.
- Ledger comparison matched 25/25 version/checksum pairs.
- Both databases contained 54 tables.
- Key counts matched: Guides 12, Guide steps 35, Knowledge articles 9.
- The complete 16-test suite passed against the restored database with zero
  failures.

Rollback/recovery remains backup-first. Never edit an applied migration or
ledger checksum to force recovery. Restore into a disposable database first,
verify the ledger/schema/application, and only then approve a recovery action.

## Baseline Defect Preserved

The pre-existing local `guidemypc_test` checksum mismatch for
`021_editor_role.sql` remains a documented local-ledger defect from Phase 0.
No historical file or ledger was changed to hide it. Fresh and restored
readiness databases pass with the current immutable migration chain.

## Commands And Results

| Command | Result |
| --- | --- |
| `powershell -File scripts/verify-public-root.ps1 -Port 8765` | Pass |
| `php scripts/check-local-setup.php` | Pass for local extensions, private `.env`, and MariaDB connection; local `.env` still prints its existing repository-root URL and must be updated by the owner when activating the required vhost |
| Fresh/repeat migration and two seed runs described above | Pass |
| `php scripts/verify.php --database=guidemypc_release_test` | Pass: 16/16 |
| Restore plus `php database/migrate.php --database=guidemypc_restore_test` | Pass: 0 applied, 25 total |
| `php scripts/verify.php --database=guidemypc_restore_test` | Pass: 16/16 |
| `git diff --check` | Pass; line-ending conversion warnings only |
| `git diff --name-only -- database/migrations` | No output |

Remaining gates belong to Phases 5 and 6: final screenshots/UML/DOCX files,
accessibility/browser evidence, strict package structure, clean extraction, and
independent final-package review.
