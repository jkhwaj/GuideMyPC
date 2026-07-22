# Final Submission Reorganization Plan

## Authority And Scope

This plan consumes:

1. The final-project guide summarized by the submission-readiness task.
2. Verified behavior at start commit
   `8dd43bcb8ef465e01f99756e48d4942ef6132059`.
3. `docs/route-contracts.md` and `docs/route-inventory.md`.
4. `docs/project-structure.md`.
5. `Tasks/project-structure-migration/README.md`.
6. `docs/submission/readiness-baseline.md` and the owner-approved scope.
7. `docs/submission/file-inventory.md`, which reconciles all 259 baseline
   tracked files.

No existing file was moved, merged, rewritten or deleted before its callers,
dependencies, route contract, tests, package role and rollback were recorded in
the inventory.

## Current Runtime Structure

The release candidate is transitional:

```text
root *.php compatibility routes
        |
        v
config.php and selected includes/ shims
        |
        +--> app/Core, app/Security and app/Features
        +--> resources/views for migrated rendering
        +--> MariaDB through mysqli

public/.htaccess -> public/index.php -> routes/*.php -> root route wrappers
public/assets/* contains all browser assets
```

The default repository-root XAMPP URL can still expose the compatibility tree,
with root `.htaccess` acting as temporary defense. The preferred target exposes
only `public/`. Root routes remain compatibility entry points until route
parity and the public-only document root pass their release gates.

## Target Runtime And Package

The runtime target remains the feature-oriented structure in
`docs/project-structure.md`. This readiness increment will not perform a
directory-only rewrite and does not approve moving any compatibility route.

The generated submission artifact, not the runtime repository, presents:

```text
GuideMyPC/
|-- frontend/
|-- backend/
|-- database/
|-- uml/
|-- docs/
|-- README.md
`-- PACKAGE-MANIFEST.txt
```

`backend/` must remain runnable with its required relative paths. Intentional
artifact duplication does not create a second source of truth.

## Planned Change Groups

### Group 1: Local Machine Configuration

| File | Action | Reason | Package effect |
| --- | --- | --- | --- |
| `opencode.json` | DELETE | Contains developer-specific Python and workspace absolute paths; no runtime or submission role. | Excluded from source/deploy/submission artifacts. |
| `.gitignore` | REWRITE | Ignore local OpenCode configuration and final generated/private artifacts. | No ignored private/generated file may enter a commit archive. |

Callers and dependencies: OpenCode only. No application route, autoload,
Apache, form, redirect, JavaScript, Sitemap, robots or test dependency exists.

Validation: JSON/tool startup from an optional local configuration, repository
cleanup audit, `BASE`, and `PKG`.

Rollback: restore `opencode.json` only as an untracked local file. Revert ignore
rules if they hide an artifact that policy requires Git to track.

### Group 2: AI Retirement

| File | Action | Coordinated dependency |
| --- | --- | --- |
| `ai.php` | DELETE | Root compatibility wrapper. |
| `resources/views/pages/ai.php` | DELETE | Rendered only by the AI controller method. |
| `includes/ai.php` | DELETE | No include, caller, route or test. |
| `app/Features/Pages/PageController.php` | REWRITE | Remove `ai()`. |
| `includes/header.php` | REWRITE | Remove legacy AI page metadata. |
| `routes/web.php` | REWRITE | Remove `ai.php` from the exact allowlist. |
| `public/robots.txt` | REWRITE | Remove the retired URL reference. |
| `tests/helpers_test.php` | REWRITE | Stop asserting an active AI page. |
| `tests/route_map_test.php` | REWRITE | Expect the approved final route set. |
| Route and submission documents | REWRITE | Record owner-approved retirement and safe 404; remove active claims. |

No current navbar, footer, Homepage, Sitemap, form, redirect, email, or
JavaScript caller exists. The removed view's Diagnostic link does not own the
Diagnostic feature; Diagnostics remains active and independently reachable.

Route contract: `GET /ai.php` changes from public placeholder HTML to the
standard safe 404. This is an explicit owner-approved retirement, not an
accidental compatibility regression.

Validation: exact reference scan, route-map test, safe 404 through
`public/index.php`, direct-root non-exposure under the public-only vhost,
Diagnostics route checks, `BASE`, `HTTP`, and `PKG` no-AI claim/file scan.

Rollback: restore the wrapper, controller method, view, map, metadata, robots,
tests and contract together. Do not restore only the route file. Historical
`database/migrations/017_ai_assistant.sql` remains immutable in both directions.

### Group 3: Donate Retirement

| File | Action | Coordinated dependency |
| --- | --- | --- |
| `donate.php` | DELETE | Root compatibility wrapper. |
| `resources/views/pages/donate.php` | DELETE | Rendered only by the Donate controller method. |
| `app/Features/Pages/PageController.php` | REWRITE | Remove `donate()`. |
| `includes/footer.php` | REWRITE | Remove Donate link from legacy routes. |
| `resources/views/partials/footer.php` | REWRITE | Remove Donate link from migrated views. |
| `includes/header.php` | REWRITE | Remove legacy Donate metadata. |
| `routes/web.php` | REWRITE | Remove `donate.php` from the exact allowlist. |
| `tests/helpers_test.php` | REWRITE | Stop asserting an active Donate page. |
| `tests/route_map_test.php` | REWRITE | Expect the approved final route set. |
| Route and submission documents | REWRITE | Record owner-approved retirement and safe 404; remove active claims. |

No other navigation, Sitemap, robots, form, redirect, email or JavaScript
caller exists. `contact.php` remains active independently.

Route contract: `GET /donate.php` changes from public placeholder HTML to the
standard safe 404.

Validation: exact reference/link crawl, both footer render paths, route-map
test, Contact smoke, safe 404 through the front controller, direct-root
non-exposure, `BASE`, `HTTP`, `UI`, and `PKG`.

Rollback: restore the wrapper, controller method, view, both footer links,
metadata, map, tests and contract as one slice.

### Group 4: Dormant Helper Cleanup

| File | Action | Evidence |
| --- | --- | --- |
| `includes/uploads.php` | DELETE | No include, function caller, route, form, multipart handler, JavaScript, test or active documentation contract. |
| `includes/maintenance.php` | DELETE | No include, function caller, route, navigation, Sitemap, robots or test. |

Generic upload storage, Apache-denial and package-exclusion rules remain
security requirements. Public Knowledge articles with maintenance content
remain public Knowledge. Category deletion continues checking historical
maintenance rows so dormant persisted data cannot be orphaned.

Validation: exact symbol/include/multipart/route scan, full suite, repository
cleanup audit, and package scan.

Rollback: restore each helper file if a real caller is discovered. Do not edit
or remove historical migrations 015 or 018.

### Group 5: Remove Unsupported Product Claims

| Area | Runtime action | Documentation/test action |
| --- | --- | --- |
| Knowledge administration | None; implementation does not exist. | Remove nonexistent route contracts and active-scope claims. Preserve public Knowledge. |
| Reports | Remove unused `Authorization::VIEW_REPORTS` and its unit-test expectations. Preserve Dashboard charts/KPIs. | Remove active Reports claims and route expectations. |
| Full-resource APIs | None; implementation does not exist. Preserve Search suggestions and event JSON endpoints. | State that only two bounded Search JSON endpoints exist; remove REST/full-resource claims. |
| Maintenance, uploads and AI | Remove dead/placeholder runtime files in earlier groups. | Mark historical plans cancelled or superseded; retain immutable schema history and valid security exclusions. |

Validation: graph/reference scan, route maps, capability matrix, public
Knowledge tests, Dashboard tests, Search JSON route tests, claim-to-code audit,
`BASE`, `HTTP`, and `PKG`.

Rollback: restore report capability and documentation only with a future
approved, implemented and tested route. Restore unsupported API or Knowledge
administration claims only after their full vertical slices exist.

### Group 6: Release And Submission Infrastructure

Planned additions or rewrites after product cleanup:

- repository cleanup audit with exact duplicate, collision, generated/local
  artifact, absolute-path and prohibited-path checks;
- strict submission packager producing the required presentation from an
  explicit clean commit;
- automated submission-structure/package tests;
- live Apache route/private-path checks;
- fast and full Composer gates that fail on missing required checks;
- documentation, UML source/exports, screenshot manifest, Word sources and
  release evidence aligned only to the validated commit.

Validation: `BASE`, `DB`, `HTTP`, `UI`, strict `PKG`, clean extraction, artifact
open checks, secret/PII scan, and independent review.

Rollback: package/build outputs are disposable and regenerated from a known
commit. Revert scripts/tests/docs together if a gate is incorrect; never weaken
a failing security or package gate merely to obtain a pass.

## Explicit Non-Actions

- No compatibility route other than owner-retired AI and Donate is deleted.
- No tracked file is moved or merged in Phase 2.
- No historical migration from 001 through 024 is edited, renamed, reordered,
  moved or deleted, including both 023 files.
- No dormant schema is dropped as cleanup.
- No clean URL, framework, ORM, SPA, template-engine or generic Admin/API
  architecture is introduced.
- No production deployment is claimed from local XAMPP evidence.
- No public Knowledge route, repository, navigation, Sitemap entry or test is
  removed with the unfinished Knowledge administration claims.
- No Search JSON endpoint is removed with the unsupported full-resource API
  claims.

## Required Test Additions Before Structural Removal

The following tests must exist or be updated in the same change group:

1. Route-map exact set after AI and Donate retirement.
2. Front-controller safe 404 for `ai.php` and `donate.php`.
3. Included static pages continue to render through their legacy URLs.
4. Contact and Diagnostics remain reachable without retired-page links.
5. Guest/user/editor/admin route checks for retained scoped features.
6. Public-root private-path probes.
7. Repository cleanup audit.
8. Submission structure and strict package tests.
9. Public Knowledge remains published-only and has no administration claim.
10. Dashboard capability/data tests pass without a Reports capability.
11. Search suggestions and event endpoints retain their bounded JSON contracts.

## Change Order And Rollback Gates

1. Add cleanup/package test infrastructure without deleting runtime files.
2. Update route contracts with approved retirements before runtime removal.
3. Retire AI as one tested slice; stop and restore the slice on failure.
4. Retire Donate as one tested slice; stop and restore the slice on failure.
5. Remove dead upload/maintenance helpers; stop and restore each file on any
   newly discovered caller or test regression.
6. Remove unsupported capabilities/claims while preserving verified features.
7. Run `BASE` and targeted HTTP checks after every group.
8. Complete public-root, database, submission-document and package phases only
   after the preceding group has recorded passing evidence.

## Phase 1 Exit Conditions

- Every baseline tracked file has one inventory row.
- No exact duplicate or case-insensitive path collision exists.
- Every planned delete/rewrite has caller, dependency, route, test, package and
  rollback evidence.
- No MOVE or MERGE is planned.
- Historical migrations are explicitly protected.
- Route retirement is owner-approved and testable.
- The working tree contains only the three explained Phase 0/1 documentation
  additions before Phase 2 begins.
