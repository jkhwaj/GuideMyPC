# Project Structure Guide

This guide governs the incremental migration described in `Tasks/project-structure-migration/`. It distinguishes the procedural application that runs today from the target structure. Target-only paths and conventions must not be represented as implemented until their migration task is complete.

## Migration Principles

- Keep PHP 8.2, MariaDB, `mysqli`, server-rendered PHP, and progressive vanilla JavaScript.
- Move one vertical feature slice at a time; do not perform a directory-only rewrite.
- Preserve canonical legacy `*.php` paths, form field names, redirects, status codes, session effects, and HTML/JSON/XML contracts. No alternate URL scheme is in verified-core scope.
- Add a controller, service, repository, validator, or query object only when it has a real responsibility.
- Keep runtime storage outside the repository and web root.
- Never edit, rename, reorder, or move applied historical database migrations.
- Treat publication, authorization, and response behavior as contracts. A structural change must not silently correct or alter them.

## Current Structure

The current runtime is procedural:

```text
repository root
|-- *.php                 web pages and actions
|-- config.php            shared procedural bootstrap facade
|-- includes/             bootstrap, helpers, security, errors, database, layouts
|-- public/assets/        public CSS and JavaScript; root rewrite aliases preserve legacy URLs
|-- database/             migrations, seeds, and CLI runners
|-- scripts/              operational CLI commands
|-- tests/                custom CLI helper and integration tests
`-- docs/ and Tasks/      project documentation and work plans
```

Root routes currently load `config.php`, which initializes configuration, session behavior, global helpers, errors, and a global `mysqli` connection. Shared templates are under `includes/`. The repository root is currently exposed by local Apache configuration; its `.htaccess` rules are a temporary defense, not the target security boundary.

## Transitional Structure

Migration tasks add the following boundaries while keeping root routes and selected `includes/` files as compatibility shims:

```text
app/
bootstrap/
config/
resources/views/
routes/
public/
```

During this state:

- `config.php` remains a temporary compatibility facade.
- Existing global functions remain wrappers until their callers migrate.
- Root route files become thin dispatchers before removal.
- New classes use the `GuideMyPC\` namespace and Composer PSR-4 autoloading.
- New views receive explicit data; they do not read global `$conn` or superglobals.
- `database/`, `scripts/`, and `tests/` remain top-level operational directories.
- `public/index.php` dispatches the approved legacy route map to root compatibility scripts until feature routes move fully under `app/`.
- Static files live under `public/assets/`; temporary rewrite aliases retain existing `/css` and `/js` URLs during the transition.

## Target Structure

```text
GuideMyPC/
|-- app/
|   |-- Core/
|   |-- Security/
|   `-- Features/
|       |-- Pages/
|       |-- Accounts/
|       |-- Guides/
|       |-- Knowledge/
|       |-- Diagnostics/
|       |-- Downloads/
|       |-- Community/
|       |-- Search/
|       |-- Dashboard/
|       `-- Home/
|-- bootstrap/
|   |-- web.php
|   |-- cli.php
|   `-- test.php
|-- config/
|-- public/
|   |-- index.php
|   |-- .htaccess
|   `-- assets/
|-- resources/views/
|-- routes/
|   |-- web.php
|   |-- admin.php
|   `-- api.php            two narrow Search JSON endpoints only
|-- database/
|-- scripts/
|-- tests/
|-- docs/
`-- Tasks/
```

Only `public/` may be exposed by the web server. Apache or Nginx must not serve `app/`, `bootstrap/`, `config/`, `database/`, `scripts/`, `tests/`, `docs/`, `Tasks/`, or Composer/project metadata unless a required public artifact is explicitly copied to `public/`.

Runtime data remains external:

```text
<PRIVATE_STORAGE_PATH>/
|-- logs/
|-- uploads/
|-- cache/
|-- sessions/
`-- rate-limits/
```

`PRIVATE_STORAGE_PATH` must be writable by the PHP process, outside the repository and web root, and excluded from source archives and version control.

The reserved `uploads/` storage name is a defensive private-path boundary, not an implemented Uploads feature.

## Dependency Rules

| Layer | May depend on | Must not depend on |
| --- | --- | --- |
| `Core` | PHP runtime and Composer dependencies | `Security`, `Features`, views, routes |
| `Security` | `Core` | `Features`, views, routes |
| `Features` | `Core`, `Security` | unrelated feature internals, route files |
| `routes` | `Core`, `Security`, feature controllers | views, SQL implementation details |
| `resources/views` | explicit view data and safe view helpers | database, redirects, request mutation |
| `database` | database runner utilities | web routes, views |
| `scripts` and `tests` | CLI/test bootstrap and explicit application services | web bootstrap or browser session behavior |

Cross-feature reads use named projections such as Home, Search, Sitemap, or the role-scoped Dashboard. Dashboard summaries and charts are read projections, not a Reports feature. Cross-feature writes use a named application service. Do not make feature repositories import each other or create a generic repository for unrelated tables.

## Responsibilities

### Core

`app/Core/` owns configuration, environment resolution, URL generation, request/response handling, database connection creation, transaction helpers, rendering coordination, logging, and safe error handling.

`bootstrap/web.php` initializes request context, error handling, security headers, and session behavior. `bootstrap/cli.php` initializes configuration, logging, and explicitly requested services without sessions or HTTP headers. `bootstrap/test.php` extends the CLI bootstrap and rejects unsafe database configuration.

### Security

`app/Security/` owns session authentication, authorization, CSRF, rate limiting, security headers, and approved HTTPS/trusted-proxy policy.

The existing session keys, CSRF behavior, flash/old-input behavior, HTTP 303 redirects, special `419` response, and bounded JSON response format remain compatibility contracts until deliberately changed with tests and a decision record.

### Features

Feature code owns request coordination, feature validation, business rules, query/command objects, and views for its product area.

- `Pages` owns static and legal pages.
- `Accounts` owns registration, login, reset, profile, settings, and account data workflows.
- `Guides` initially owns categories, guide reads, steps, progress, favorites, and ratings because their current lifecycle is coupled.
- `Knowledge` owns public articles, glossary, and error-code content. Knowledge administration is not implemented.
- `Diagnostics` owns the working diagnostic session and transition flow. Both navigation implementations link to it; outcome transitions set `completed_at`, while back and restart clear completion as state is recomputed.
- `Downloads` owns public eligibility, trusted URL validation, and download administration after its policy is approved.
- `Community` owns only the approved canonical `community_posts`, `community_comments`, and `community_likes` model.
- `Search` and `Home` are explicit cross-feature read models. Search exposes only its two documented bounded JSON endpoints.
- `Dashboard` owns role-scoped personal or operational KPI, chart, and recent-activity projections. It does not own report generation or exports.

Administration belongs to the feature that owns the data. Shared administrator authorization and audit recording belong in `Security` or `Core`; a monolithic admin data layer is not a target.

Historical migrations, dormant schema, or defensive dependency checks for AI, maintenance, uploads, confidence, or Community v2 do not make those areas release capabilities. AI Assistant, Uploads, Maintenance Center, Community v2, Knowledge administration, product Reports, full-resource APIs, mail delivery, and CSV export are outside verified-core scope.

### Views

Views are plain PHP files under `resources/views/`. They receive explicit view data and render HTML only. They must not open database connections, issue SQL, redirect, mutate sessions, authorize requests, or start output before controller processing has completed.

Pass explicit page metadata to layouts. Do not infer the logical page from `$_SERVER['SCRIPT_NAME']`, because a front controller makes that value `index.php` for every route.

### Database and Scripts

Keep `database/` top-level. The migration runner sorts and checksum-records ordered SQL files. Add corrections as new forward migrations and test both fresh installation and representative upgrade paths.

Keep `scripts/` top-level. Operational commands use CLI bootstrap and must not emit browser sessions, headers, or HTML. Tests use an explicitly configured isolated database and must refuse the normal application database.

## Routing and Compatibility

For the verified-core release:

- Existing `*.php` paths remain canonical.
- Existing query/form field names and HTTP methods remain supported.
- Password-reset links, navigation, JavaScript requests, sitemap URLs, robots rules, and canonical metadata retain compatible paths.
- A front controller maps legacy paths to named routes without replacing or redirecting their canonical paths.
- Route metadata comes from the named route, not a physical script filename.

See `docs/route-contracts.md` for the baseline route groups and compatibility rules.

## Response and Security Contracts

- Browser mutations use POST, CSRF validation, authorization, validation feedback, flash messages, and PRG unless a documented endpoint contract says otherwise.
- JSON is limited to explicitly documented route contracts; `routes/api.php` contains only the two narrow Search endpoints and is not a full-resource API.
- JSON success responses contain `ok: true`, `data`, and `meta.request_id`.
- JSON failures contain `ok: false`, bounded `error.code` and `error.message`, and `meta.request_id`.
- Browser and JSON responses never expose SQL, stack traces, filesystem paths, credentials, private content, or another user's data.
- Rate limits use private storage and must not be broadly changed while extracting security code.
- Download and Community publication policy must be centralized only after their decisions are approved.

## Decision Register

| Decision | Status | Safe migration rule | Blocking tasks |
| --- | --- | --- | --- |
| Legacy URL compatibility | Approved | Preserve canonical `*.php` paths and form contracts; no alternate URL scheme is in scope. | None |
| Private runtime storage | Approved | Keep it configurable and external to repository/web root. | None |
| Canonical Community model | Approved | Keep the active `community_posts`, `community_comments`, and `community_likes` model. Public projections require `is_published = 1`; defer the unwired question/answer schema. | None |
| Public Download eligibility | Approved | Public records require `is_published`, `review_state = 'approved'`, and a safe HTTPS URL under the existing trusted-URL policy. | None |
| Artifact Composer strategy | Approved | Build local artifacts with the locked Composer autoloader; keep source archives dependency-free. Artifact creation is not production-hosting proof. | `001`, `008`, `009` |
| Trusted proxy and HTTPS policy | Approved | Support direct Apache HTTPS only; do not trust forwarded headers without a separately approved allowlist. | `002`, `008` |

Each pending decision needs an owner, deadline, and decision record before its blocking task can pass. The Community and Download decisions were approved by the project owner on 2026-07-18.

## Completion Rules

Remove a compatibility shim only when all callers, scripts, tests, and deployment paths have migrated and the route-contract suite passes. Update this guide, `docs/application-conventions.md`, the system overview, deployment documents, and architecture diagrams whenever the implemented structure changes.
