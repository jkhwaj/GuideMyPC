# Final Submission Readiness Baseline

## Phase 0 Record

| Field | Value |
| --- | --- |
| Date | 2026-07-22 |
| Branch | `final-project-submission-readiness` |
| Start commit | `8dd43bcb8ef465e01f99756e48d4942ef6132059` |
| Base branch | `main` |
| Initial tracked files | 259 |
| Initial worktree | Clean (`git status --short --branch`) |
| Initial diff check | Pass (`git diff --check`) |
| Application database category | Local development database; credentials not recorded |
| Configured test database | `guidemypc_test` |
| Clean baseline test database | `guidemypc_readiness_test` |

The requested readiness guide was read in full from
`origin/agent/organize-project-folders:Tasks/final-project-submission-readiness/README.md`.
That file is not present at the start commit on `main`. Its requirements govern
this readiness work, subject to the higher-priority final-project guide and the
verified release behavior.

## Verified Toolchain

| Component | Baseline version |
| --- | --- |
| PHP CLI | 8.2.12 |
| Apache | 2.4.58 (Win64) |
| MariaDB | 10.4.32 |
| Composer | 2.10.2 |
| Git | 2.55.0.windows.2 |
| Google Chrome | 150.0.7871.130 |
| Operating environment | Windows with XAMPP |

## Baseline Commands

| Command | Result |
| --- | --- |
| `git status --short --branch` | Pass; clean branch |
| `git diff --check` | Pass |
| `C:\xampp\php\php.exe scripts\check-local-setup.php` | Pass; required extensions, private `.env`, and local MariaDB connection available |
| `composer validate --strict` | Pass |
| `composer install --no-interaction` | Pass; lock contents installable, no package changes required, autoload generated |
| `composer run verify:fast` | Pass; Composer validation, 150 tracked PHP files linted, helper tests passed |
| `C:\xampp\php\php.exe database\migrate.php --database=guidemypc_test` | Fail; local ledger reports that historical `021_editor_role.sql` changed after application |
| `C:\xampp\php\php.exe database\migrate.php --database=guidemypc_readiness_test` | Pass; 25 migrations applied to a new isolated database |
| `C:\xampp\php\php.exe database\seed.php --database=guidemypc_readiness_test` | Pass; three seed files processed and 12 guide search documents rebuilt |
| `C:\xampp\php\php.exe scripts\verify.php --database=guidemypc_readiness_test` | Pass; 14 test files, zero failures |

The `guidemypc_test` checksum failure is preserved as baseline evidence. No
historical migration will be edited to repair local state. Release validation
uses a clean isolated database ending in `_test`; recovery of the stale local
ledger must follow `database/recovery.md`.

## Approved Final Scope

The project owner approved a verified-core release. The release includes only
features demonstrated by working code and repeatable tests. Public Knowledge
reads remain in scope.

The following are excluded from final-release claims and active product entry
points unless later implementation and evidence prove them complete:

- AI Assistant.
- Uploads.
- Maintenance Center.
- Knowledge administration; public Knowledge remains active.
- Reports.
- Unproven full-resource APIs.
- Donate.

AI Assistant and Donate are explicitly removed from final product scope. Their
old URLs must return the standard safe 404 response. Before removal, Phase 1
must record callers, dependencies, route maps, forms, redirects, JavaScript,
tests, sitemap/robots references, documentation and package roles. The route
contracts must record the approved retirement.

The scope freeze excludes clean-URL work, framework or SPA migration, ORM or
template-engine adoption, production-hosting claims, Community v2, password
mail delivery, CSV export, and all other optional features not proven in the
release.

## Phase 0 Exit

- Baseline commands and existing failures are documented.
- A dedicated isolated test database is identified and passes the unchanged
  suite.
- Final feature scope is owner-approved.
- The starting worktree was clean; this evidence file is the first explained
  readiness-worktree change.

Phase 0 is complete. Phase 1 must finish the tracked-file inventory and the
reorganization plan before any move, merge, rewrite, or deletion.
