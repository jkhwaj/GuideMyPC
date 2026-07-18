# GuideMyPC Final Project MVP Plan

- Status: In progress
- Target branch: `final-project-mvp`
- Deadline: 31 July 2026 at 23:59
- Input: `GuideMyPC_Final_Project_Implementation_Plan.md`
- Governing architecture plan: `Tasks/project-structure-migration/README.md`

## Objective

Deliver a focused, testable academic MVP without creating a second application architecture. Final-project features must advance the existing compatibility-first migration, preserve legacy route and response contracts, and use the documented PHP 8.2, MariaDB, `mysqli`, server-rendered PHP, and vanilla JavaScript stack.

The final submission package is a release artifact. Its `frontend/`, `backend/`, `database/`, `uml/`, and `docs/` presentation must not be imposed on the runtime repository. The runtime repository continues toward the structure in `docs/project-structure.md`; packaging maps approved release files into the required submission ZIP only after validation.

## Planning Baseline

The repository is a working procedural prototype with a partial feature-oriented extraction:

- Root `*.php` routes, `config.php`, global `$conn`, and selected `includes/` files are still compatibility boundaries.
- `app/` currently contains focused Core, Accounts, Community, Downloads, Knowledge, Pages, and Search classes.
- Only the About page has completed the new controller/view boundary.
- Tasks `000` through `009` in `Tasks/project-structure-migration/` are marked `In progress`; task `010` is blocked.
- Authentication supports `user` and `admin`; the database role is an immutable historical `ENUM` in migration `001`.
- `admin.php` already has counts, recent users/posts, and top-rated guides, but embeds SQL and rendering in one admin-only route.
- There is no Chart.js dependency, dashboard read model, reports route, CRUD API, knowledge administration, or complete route-contract test suite.
- Basic CRUD exists for categories, guides, downloads, users, posts, and comments, but permission, validation, audit, filtering, and test coverage are inconsistent.
- Knowledge is read-only. Guide steps are edited only as nested guide data and replacement can affect saved progress.
- The active Community model is `community_posts`, `community_comments`, and `community_likes`. The question/answer tables from migration `019` remain deferred.
- `scripts/verify.php` currently skips missing expected tests and is not a complete release gate.
- The repository root remains the local Apache document root; task `008` must move exposure to `public/` as one coordinated routing and asset change.

## Non-Negotiable Decisions

1. Preserve the legacy `*.php` URLs, form names, redirects, status codes, session keys, and HTML/JSON/XML contracts documented in `docs/route-contracts.md`.
2. Do not edit migrations `001` through `020`. Add forward migrations beginning with the next available sequence number.
3. Do not create a parallel generic Admin architecture. Administrative commands remain with the feature that owns the data; shared authorization and audit behavior belong to Security/Core.
4. Do not activate Community v2, AI Assistant, secure uploads, or the Maintenance Center for the final MVP.
5. Do not expose unfinished features in navigation, homepage calls to action, sitemap, screenshots, or report claims.
6. Do not activate `public/` incrementally. The front controller, named legacy routes, assets, generated URLs, Apache configuration, and private-path tests land together under migration task `008`.
7. Do not claim analytics the schema cannot support. Labels and report definitions must state whether a value is a current snapshot, an authenticated-user event count, or a historical total.
8. Do not create or seed production credentials. Test users and content are deterministic, sanitized fixtures in the isolated test database or documented local demonstration setup.
9. Additive product routes such as `dashboard.php`, knowledge administration, reports, and APIs require route-contract entries before implementation.
10. The branch `final-project-mvp` must be created from the approved, merged structural-migration baseline. Do not develop the same migration work independently on two long-lived branches.

## Scope Freeze

### Required MVP

- Guest, registered user, editor, and administrator journeys
- Database roles `user`, `editor`, and `admin`
- Centralized authorization with explicit capabilities
- Working authentication, profile, settings, progress, favorites, and ratings
- Working public Guides, Knowledge, Downloads, Diagnostics, Search, and canonical Community features
- Complete administration for Categories, Guides with steps, Knowledge Articles, Downloads, Users, Community Posts, and Community Comments
- Shared responsive dashboard presentation with role-aware navigation
- Six defined KPI cards, four required charts by final release, and permission-filtered recent activity
- Reports page with KPI summaries, tables, and two or three charts
- JSON API for the approved core resources and reports
- Repeatable migrations, seeds, isolated tests, route checks, and packaging validation
- Public-only document root, private runtime storage, security headers, CSRF, prepared statements, authorization, and safe errors
- Final screenshots, four UML diagrams, separate `Readme.docx`, final Word report, source archive, and clean submission ZIP

### Explicit Cuts

- AI Assistant
- Secure uploads
- Maintenance Center
- Community question/answer/report tables from migration `019`
- OAuth account linking
- Automatic password-reset email delivery unless a safe test mail transport is completed early
- CSV export unless all required reports and tests are complete
- Clean URLs or redirects away from legacy `*.php` paths
- Framework, ORM, React, Node.js, or template-engine migration
- Production hosting work beyond documented, validated deployment instructions

## Role And Capability Model

Use explicit capabilities instead of scattered string comparisons or a numeric role hierarchy.

| Capability | User | Editor | Admin |
| --- | ---: | ---: | ---: |
| View personal dashboard and account data | Yes | Yes | Yes |
| View published content | Yes | Yes | Yes |
| Create and update owned feature content | No | Yes | Yes |
| Submit, publish, archive, and moderate content | No | Yes | Yes |
| View content reports | No | Yes | Yes |
| Delete managed records | No | No | Yes |
| Manage users, roles, and account status | No | No | Yes |
| View user registration details and administrative audit activity | No | No | Yes |
| Configure system-level settings | No | No | Yes |

The implementation should expose compatibility helpers such as `is_editor()`, `require_editor()`, and `require_admin()` while delegating decisions to a namespaced Security authorization class. `require_admin()` remains strict; adding `editor` must not widen existing user-management or delete routes accidentally.

## Route Strategy

Update `docs/route-contracts.md` before adding routes. Preserve all current routes and add only the minimum required routes:

- `dashboard.php`: authenticated role-aware dashboard entry point
- `admin_knowledge.php`, `add_knowledge.php`, `edit_knowledge.php`, and `delete_knowledge.php`: Knowledge administration
- `admin_reports.php`: editor/admin report view with admin-only audit details
- `/api/*.php` legacy-compatible JSON endpoints initially, mapped to named routes during task `008`

Existing content administration routes may admit editors only after each route is split into capability-safe actions. Admin-only user management and deletion contracts remain unchanged.

## Dashboard Metric Definitions

Use definitions that the current schema can calculate consistently:

1. **Published guides:** `guides.is_published = 1`.
2. **Registered users:** aggregate count of active, non-deleted users. Editors may see the aggregate count but not user identities or account details.
3. **Guide completions this month:** distinct user/guide pairs for which all current guide steps are completed and the latest step completion falls in the current calendar month. Label this as a current completion snapshot, not immutable history.
4. **Published knowledge articles:** `knowledge_articles.publication_state = 'published'`.
5. **Approved downloads:** records satisfying the centralized public Download policy, not merely `review_state = 'approved'`.
6. **Published community posts:** canonical `community_posts.is_published = 1` records.

The first two charts are **content by category** and **user registrations by month**, because both are supported by existing timestamps. Add the remaining required charts only with honest sources:

- **Guide views by month:** initially authenticated guide-view activity from `user_activity`; rename the chart accordingly unless an approved aggregate view-event migration is added.
- **Guide completion rate:** current users with all guide steps complete divided by users who started at least one step, with zero-denominator handling.

The six operational KPI cards are for editor and administrator dashboard projections. Registered users receive personal progress, favorites, and activity rather than system-wide operational metrics. Recent activity must be permission filtered: editors may see recent published guides and public/moderated Community content, while only administrators may see recent user registrations and administrative audit events.

## Delivery Sequence

### Milestone 0: Approve Contracts And Baseline

**Goal:** freeze the product and route behavior before structural or schema changes.

**Consumes:** migration tasks `000`, `001`, and the current implementation evidence.

**Work:**

- Approve this scope, role matrix, dashboard metric definitions, and route additions.
- Record baseline outcomes for authentication, all current admin routes, navigation, `admin.php`, and dashboard data queries.
- Reconcile `README.md` and submission documentation with the existing Composer manifest and transitional architecture.
- Configure, migrate, and seed a dedicated `_test` database so the current suite can run.
- Create `final-project-mvp` from the approved baseline only after planning changes are merged.

**Exit gate:** route additions and role behavior are documented; current tests run against an isolated database; no unresolved policy decision blocks Milestone 1.

### Milestone 1: Editor Role, Navigation, And Dashboard Foundation

**Goal:** deliver the first vertical slice requested by the draft without widening administrator-only behavior.

**Alignment:** complete the applicable `002` and `003` foundations first, then implement this approved product increment within the boundaries owned by `006` and `007`. Add focused tests immediately as required by every migration phase; this advances but does not complete the downstream `009` release gate, which still depends on task `008`.

The dashboard visual redesign is an additive final-project requirement and currently exceeds task `007`'s non-goal of redesigning the dashboard. Update and approve task `007` scope before implementation rather than silently treating the redesign as structural migration work.

**Schema:**

- Add a new migration, expected as `database/migrations/021_editor_role.sql`, that changes the `users.role` domain to `user`, `editor`, and `admin` while preserving existing values and the `user` default.
- Test fresh migration, representative upgrade from migration `020`, and a second no-op migration run.

**Authorization:**

- Add a namespaced capability policy under `app/Security/`.
- Keep global helpers in `includes/security.php` as tested compatibility delegates while task `002` is active.
- Validate role input server-side in `edit_user.php`; add the Editor option; reject unknown roles before SQL.
- Retain strict admin protection on user management, role changes, deletion, and audit details.
- Update login/session characterization to prove all three role strings survive login and session regeneration.

**Navigation:**

- Remove the AI Assistant link from both `includes/navbar.php` and `resources/views/partials/navbar.php`.
- Remove the planned AI Assistant homepage call to action from `index.php` while retaining the legacy `ai.php` route until route migration decides whether it remains directly reachable.
- Pass explicit capability-based navigation data to views; do not infer authorization from presentation labels.
- Keep both navbar implementations synchronized until the legacy partial is retired by task `010`.

**Dashboard:**

- Add an explicit cross-feature Dashboard read model under `app/Features/` as part of task `007`; it owns read-only KPI, chart, and activity projections and applies feature publication policies.
- Add a controller and plain PHP dashboard views under `resources/views/`, including a reusable dashboard layout/shell.
- Keep database access, authorization, and redirects out of views.
- Keep `admin.php` admin-only until its exact output and private fields are separated. Add `dashboard.php` as the role-aware entry point and document it before implementation.
- Render six defined KPI cards, content-by-category and registrations-by-month charts, and permission-filtered recent activity.
- Select and pin one Chart.js version during implementation, serve it locally, retain its license, and record the exact version and use in `docs/submission/third-party-inventory.md`.
- Add only the CSS and JavaScript required for accessible cards, charts, sidebar/top bar behavior, keyboard operation, reduced motion, empty/error states, and a 320px layout.

**Expected files:**

- `database/migrations/021_editor_role.sql`
- `app/Security/` authorization policy
- `includes/security.php`
- `edit_user.php`
- `login.php` only if required by the centralized authorization extraction
- `docs/route-contracts.md`
- `app/Features/` Dashboard read model and controller
- `dashboard.php`
- `admin.php`
- dashboard views and layout under `resources/views/`
- `includes/navbar.php`
- `resources/views/partials/navbar.php`
- `index.php`
- `includes/header.php` or explicit view metadata, as applicable
- `css/style.css` and `css/design-system.css`, or narrowly scoped dashboard assets selected during task `008`
- `js/script.js` or a narrowly scoped dashboard script
- locally pinned Chart.js asset and license
- `tests/authorization_test.php`
- `tests/dashboard_integration_test.php`
- route-contract tests for `dashboard.php` and unchanged `admin.php`
- `scripts/verify.php`
- affected architecture, technology-inventory, and task evidence documents

**Required tests:**

- Guest receives the approved unauthenticated outcome for `dashboard.php`.
- User can access only the personal dashboard projection and cannot access content/admin controls.
- Editor can access content dashboard projections but not user details, user management, delete actions, or audit metadata.
- Admin retains all existing admin access.
- Unknown or tampered roles receive no privileged capability.
- Existing `require_admin()` callers remain admin-only.
- KPI publication filters and zero-data states are correct.
- Download count uses the centralized eligibility policy.
- Chart datasets are valid JSON, bounded, ordered by month/category, and safe for empty data.
- `scripts/verify.php` fails if any required test file is absent.
- Desktop keyboard navigation, tablet layout, and 320px mobile layout are manually recorded until browser automation exists.

**Exit gate:** all role and dashboard tests pass; existing route contracts remain stable; two initial charts and recent activity render without leaking admin-only data; desktop and 320px evidence is recorded.

### Milestone 2: Complete Feature-Owned CRUD

**Goal:** make every required management workflow complete before adding broad APIs.

**Consumes:** migration tasks `004`, `005`, and `006`.

Implement vertical slices in this order:

1. Categories and Guides, including publication controls, sources, and safe guide-step add/remove/reorder behavior.
2. Knowledge Articles, including the new route contract, publication lifecycle, sources/tags as required, and audit events.
3. Downloads, including all fields required for approved review/publication policy and consistent audit events.
4. Users, including role/status validation and safe restrictions on self-demotion, last-admin removal, and deletion.
5. Canonical Community posts and comments, including edit/moderation/publication behavior without activating Community v2.

Every listing receives server-side search, allowlisted filters/sorts, pagination, page size limits, empty/error states, and accessible table behavior. Every mutation receives server-side validation, old input, CSRF, capability authorization, prepared SQL, PRG redirects, success/error flashes, destructive confirmation, and audit recording where applicable.

Guide-step replacement needs a specific decision and test before implementation because replacing rows can cascade saved `user_progress`. Do not advertise standalone guide-step CRUD until progress-preserving add, update, reorder, and delete semantics are approved.

**Exit gate:** complete guest/editor/admin route matrices pass for each CRUD slice; at least one sanitized entity has more than 25 records proving pagination; no route starts output before authorization, validation, or failure-prone queries complete.

### Milestone 3: APIs And Reports

**Goal:** expose stable feature behavior through bounded JSON and build reports from named read models.

**Consumes:** stable Milestone 2 feature services and migration task `007`. Additive root JSON endpoints may be implemented under documented contracts first; task `008` later maps them to named routes when the public front controller is activated.

**API:**

- Add Categories, Guides, Knowledge, Downloads, Users, and Reports endpoints only after their browser workflows have reusable validation and authorization boundaries.
- Support `GET` list/get and applicable `POST`, `PUT`, and `DELETE` methods. If Apache/PHP integration requires action-based POST fallback, document it as the actual contract rather than claiming unsupported methods.
- Allowlist `page`, `limit`, search, sort, direction, and resource-specific filters; cap page size.
- Preserve the standard `ok`, `data`/`error`, and `meta.request_id` response shape.
- Apply authentication, capability authorization, session CSRF policy, validation, prepared statements, rate limits where relevant, safe errors, and audit events.

**Reports:**

- Add `admin_reports.php` for editors/admins, with admin-only user/audit detail.
- Implement most-viewed and highest-rated guides, current guide completions, content by category, user registrations by month, Download review totals, and canonical Community activity by month.
- Use two or three pinned Chart.js visualizations plus accessible data tables.
- Treat CSV as optional and omit it if release gates are incomplete.

**Exit gate:** method, authentication, authorization, validation, pagination, error, and request-ID tests pass for every endpoint; report totals match deterministic fixtures and do not expose unpublished/private content.

### Milestone 4: Public Root And Release Hardening

**Goal:** complete migration tasks `008` and `009` as one release gate.

- Activate named routes and the `public/` front controller while retaining legacy paths.
- Move assets under `public/` and verify all form, JavaScript, sitemap, reset-link, canonical, and base-path references.
- Expose only `public/`; probe source, configuration, database, script, test, task, documentation, Composer, and private runtime paths.
- Make seeds repeatable and sufficient for search, filtering, sorting, pagination, dashboard, reports, and screenshots.
- Correct seed/schema issues only with forward migrations. In particular, ensure demonstration Downloads satisfy the approved public policy.
- Add one fast verification command and one complete release command covering required-file checks, PHP lint, Composer validation, migrations, repeated seed, integration tests, HTTP route matrix, private-path probes, and package checks.
- Build and test a clean deployment artifact using locked production dependencies.

**Exit gate:** clean install, representative upgrade, repeated migration/seed, all automated tests, Apache route matrix, private-path probes, backup restore, and rollback procedure pass with recorded evidence.

### Milestone 5: Documentation And Submission

**Goal:** generate evidence from the exact validated release, then complete task `010` sign-off.

- Synchronize `README.md`, project structure, application conventions, deployment, report, inventory, and diagrams with implemented behavior.
- Create four UML source files and PNG/PDF exports using only active runtime entities and routes.
- Create the architecture diagram from the actual public/front-controller/application/database flow.
- Capture 12 to 15 numbered, captioned screenshots, including 320px mobile and permission/error states, using sanitized realistic data.
- Produce separate `Readme.docx` and final Word report.
- Sanitize tracked team/contact information according to course policy before packaging.
- Build the source archive from an approved release commit; exclude secrets, private data, logs, uploads, local dependencies, test artifacts containing sensitive values, and debugging utilities.
- Extract the final package to a clean directory and execute only the included installation instructions.

**Exit gate:** all four mandatory submission file types are present; the clean extraction installs and passes the release command; the final ZIP contains no secrets or unintended personal data.

## Target Calendar

This calendar has no contingency and is viable only if the scope freeze is enforced. A missed exit gate triggers scope reduction from the explicit optional cuts; it does not permit skipping security, compatibility, or submission validation.

| Date | Target | Stop/go gate |
| --- | --- | --- |
| 18-19 July | Milestone 0 | Contracts, branch base, test database, and role/dashboard definitions approved |
| 20-22 July | Milestone 1 | Three-role authorization and dashboard foundation pass focused tests and 320px review |
| 23-25 July | Milestone 2 | Required feature CRUD route matrices pass; no unresolved guide-progress or Community policy issue |
| 26-27 July | Milestone 3 | Required API and report contracts pass deterministic integration tests |
| 28-29 July | Milestone 4 | Clean install/upgrade, public-root, route, private-path, and release gates pass |
| 30 July | Milestone 5 evidence | Final screenshots, UML exports, Word documents, inventories, and source artifact complete |
| 31 July | Submission verification | Clean extraction and mandatory-file/secret/PII checks pass before 23:59 |

If Milestone 2 is not complete by 25 July, do not start optional CSV, mail transport, extra report visualizations, or dormant features. If Milestone 4 is not complete by 29 July, freeze feature changes and use 30-31 July only for release correction and required submission evidence.

## Verification Commands

These commands exist today and form the baseline. Milestone 4 must consolidate them into documented fast and release commands.

```powershell
C:\xampp\php\php.exe scripts\check-local-setup.php

C:\xampp\php\php.exe database\migrate.php
C:\xampp\php\php.exe database\seed.php

C:\xampp\php\php.exe database\migrate.php --database=guidemypc_test
C:\xampp\php\php.exe database\seed.php --database=guidemypc_test

C:\xampp\php\php.exe tests\helpers_test.php
C:\xampp\php\php.exe scripts\verify.php

composer validate
git diff --check
```

For every increment, record:

- the exact command, date, branch, and commit;
- database target category without credentials;
- pass/fail result and relevant counts;
- manual browser URL, actor role, viewport, and outcome;
- known exclusions or blockers, never a silent skip.

## Implementation Evidence

### 2026-07-18: Milestone 1 Foundation

- Branch: `final-project-mvp`; evidence recorded from the uncommitted implementation workspace.
- Added forward migration `021_editor_role.sql`; the application database applied one migration and the dedicated `guidemypc_test` database applied all 21 migrations from a fresh schema.
- A second test-database migration run reported `0 applied, 21 total`.
- Seeded the dedicated test database with both sanitized seed files.
- `C:\xampp\php\php.exe scripts\verify.php --database=guidemypc_test`: all seven suites passed, including authorization and personal/editor/admin dashboard projections.
- Repository-wide `php -l`: all PHP files passed.
- `node --check js\script.js`: passed.
- `git diff --check`: passed; line-ending conversion warnings only.
- Local Chart.js 4.5.0 SHA-512 matched the published cdnjs integrity value and Apache returned HTTP 200 for `js/chart.umd.min.js`.
- Guest HTTP characterization: `dashboard.php` returned HTTP 303 with `Location: login.php`; unchanged `admin.php` returned HTTP 403.
- `composer validate --no-check-publish` was not run because `composer` is not installed on the current PATH.
- Authenticated user/editor/admin browser rendering, keyboard behavior, and desktop/320px viewport checks remain pending manual evidence. Milestone 1 is not release-complete until these checks and route-level authenticated characterization are recorded.

### 2026-07-18: Milestone 2 Categories And Guide-Step Safety

- Status: implementation workspace after commit `eb80d00`; this increment is not yet committed.
- Category list/create/update routes now admit editors through the approved content capability while hard deletion remains administrator-only.
- Category validation covers schema limits, slug format, publication state, featured order, and duplicate slugs. Mutations and audit events share transactions.
- Category deletion checks Guides, Knowledge, Diagnostics, Maintenance, and deferred Community references. Forward migration `022_category_reference_integrity.sql` normalizes legacy category key types and adds restrictive foreign keys.
- Migration `022` passed both a fresh 22-migration install in `guidemypc_m2_test` and the representative local application upgrade; a second test migration run reported `0 applied, 22 total`.
- Category integration coverage uses 27 bounded pagination fixtures and verifies validation, publication, filtering, dependency blocking, cleanup, and audit. Two consecutive focused runs passed without relying on transaction rollback around application-owned transactions.
- Category publication now also suppresses category-owned published diagnostic flows.
- Guide editing now submits stable step IDs and synchronizes rows in place. Reordering, text edits, and additions preserve progress for retained IDs; intentional removal cascades only the removed step's progress.
- Guide tests cover mixed reorder/add/delete, contiguous numbering, no-op writes, cross-guide and duplicate-ID rejection, tampered-submit atomicity, and guide-scoped guest-progress merging.
- `C:\xampp\php\php.exe scripts\verify.php --database=guidemypc_m2_test`: all eight suites passed.
- Repository-wide PHP lint, `node --check js\script.js`, `node --check js\guide-editor.js`, and `git diff --check` passed.
- Remaining before the Guide slice is complete: editor-safe Guide route contracts, draft/publication controls, sources, bounded Guide listing, dependency-aware hard deletion, route-level role/CSRF/PRG tests, and authenticated browser evidence.

### 2026-07-18: Milestone 2 Guide Administration

- Status: implementation workspace after commit `99ae867`; this final Guide validation increment is not yet committed.
- `GuideAdminRepository` supplies bounded Guide listing, allowlisted filters/sorts, category projections, source reads, and slug checks.
- `GuideAdminService` validates Guide metadata, curation, official HTTPS sources, publication state, and structured steps; all mutations and audit events share a transaction.
- Editors can list, create, edit, draft, and publish Guides. Hard deletion remains administrator-only and is blocked by progress, favorites, ratings, and knowledge relations.
- Root Guide routes now remain compatibility entry points over the feature service and shared editor form.
- `tests/guide_admin_integration_test.php` covers validation, draft create, source replacement, bounded listing, publication, audit, dependency-blocked deletion, and unused deletion.
- Pending release evidence: route-level guest/user/editor/admin and CSRF/PRG checks, browser keyboard/desktop/320px checks, and a separate Migration 2 full verification commit.

## Critical Path And Scope Control

The critical path is:

```text
contracts
-> centralized authorization and editor migration
-> dashboard foundation
-> stable feature CRUD
-> cross-feature reports and completed dashboard analytics
-> APIs and named routes
-> public-only document root
-> release hardening
-> final evidence and package
```

If schedule pressure occurs, cut optional CSV export, mail delivery, extra charts, and nonessential fields before weakening authorization, route compatibility, test isolation, public-path protection, required CRUD, required API behavior, UML, or submission files. A smaller verified feature set must be represented honestly in the report; dormant foundation tables and placeholder pages do not count as completed features.

## Plan Definition Of Done

This plan is ready for implementation when:

- the scope freeze and explicit cuts are approved;
- the role/capability matrix is approved;
- dashboard metric and chart definitions are approved;
- new route contracts are accepted;
- the branch base and merge strategy are agreed;
- a dedicated migrated and seeded test database is available;
- Milestone 1 file impacts, tests, and validation evidence are accepted;
- no work item conflicts with `docs/project-structure.md`, `docs/route-contracts.md`, or the dependency order in `Tasks/project-structure-migration/README.md`.
