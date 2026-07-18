# GuideMyPC Project Structure Migration Roadmap

This directory defines the compatibility-first migration from the current procedural PHP layout to a small feature-oriented application structure. The migration retains PHP 8.2, MariaDB, `mysqli`, server-rendered PHP, and the existing product behavior.

The work is a strangler migration, not a rewrite. Existing routes remain operational while one vertical slice at a time moves behind namespaced application code.

## Migration Outcomes

- Only `public/` is exposed by Apache or another web server.
- New PHP classes use Composer PSR-4 autoloading under `app/`.
- Web, CLI, and test processes have separate bootstrap entry points.
- SQL, request coordination, security policy, and HTML rendering have explicit boundaries.
- Existing `*.php` URLs, form fields, redirects, status codes, session effects, and response formats remain compatible until an independently approved URL migration.
- Logs, uploads, sessions, caches, and rate-limit data remain in configurable private storage outside the repository and web root.
- Historical database migrations retain their filenames and checksums.
- Compatibility wrappers are removed only after route-level parity is proven.

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
|-- resources/
|   `-- views/
|-- routes/
|   |-- web.php
|   |-- admin.php
|   `-- api.php
|-- database/
|-- scripts/
|-- tests/
|-- docs/
|-- Tasks/
|-- composer.json
|-- composer.lock
`-- README.md
```

Runtime data is not part of the repository structure:

```text
<PRIVATE_STORAGE_PATH>/
|-- logs/
|-- uploads/
|-- cache/
|-- sessions/
`-- rate-limits/
```

During migration, root route scripts, `config.php`, and selected files under `includes/` remain temporary compatibility entry points.

## Architecture Constraints

1. `Core` must not depend on a feature.
2. `Security` may depend on `Core`, but not on a feature.
3. Features may depend on `Core` and `Security`.
4. Views must not access the database or perform redirects.
5. Repositories and query objects own SQL, not rendering or request handling.
6. Controllers coordinate requests and responses; services are added only for reusable business rules.
7. Cross-feature writes require a named application service. Cross-feature reads use explicit read models.
8. Administration remains owned by each feature and shares authorization and audit services.
9. Historical migration files are immutable; corrections use new migrations.
10. A structural move must not silently change publication, authorization, or response behavior.

## Migration Releases

| Release | Goal | Tasks |
| --- | --- | --- |
| M0 | Document contracts and establish bootstrap boundaries | `000`-`001` |
| M1 | Extract shared Core and Security behavior | `002` |
| M2 | Establish rendering boundaries | `003` |
| M3 | Migrate lower-risk and user-state features | `004`-`005` |
| M4 | Resolve policy-sensitive features and aggregators | `006`-`007` |
| M5 | Expose only the public document root | `008` |
| M6 | Harden delivery and remove compatibility code | `009`-`010` |

## Execution Rules

1. Complete tasks in numeric order unless a task explicitly permits parallel validation work.
2. Record baseline behavior before changing the code that implements it.
3. Keep each migration step deployable and reversible.
4. Do not combine clean URLs, schema redesign, ORM adoption, a template engine, or a framework migration with this work.
5. Keep legacy route names canonical through task `008`; redirects require a later explicit decision.
6. Add or update tests in every phase. Task `009` consolidates the release gate and does not defer earlier testing.
7. Run each task's validation and record evidence before marking it complete.
8. Update `docs/project-structure.md`, architecture diagrams, deployment documentation, and task status as implementation changes become real.
9. Do not remove a compatibility wrapper while any route, script, test, or operational command still uses it.
10. Stop an affected feature migration if its data or publication policy is unresolved.

## Task Index

- [`000-architecture-contract-and-route-inventory.md`](000-architecture-contract-and-route-inventory.md): freeze route behavior, resolve architecture decisions, and establish the structural guide
- [`001-composer-and-bootstrap-boundaries.md`](001-composer-and-bootstrap-boundaries.md): add PSR-4 and separate web, CLI, and test initialization
- [`002-core-security-and-compatibility-layer.md`](002-core-security-and-compatibility-layer.md): extract shared runtime and security behavior behind temporary wrappers
- [`003-view-system-and-static-pages.md`](003-view-system-and-static-pages.md): introduce plain PHP views and migrate static pages
- [`004-knowledge-and-guide-read-models.md`](004-knowledge-and-guide-read-models.md): extract Knowledge and guide read paths
- [`005-guide-actions-accounts-and-diagnostics.md`](005-guide-actions-accounts-and-diagnostics.md): migrate stateful guide, account, and diagnostic behavior
- [`006-downloads-community-and-feature-admin.md`](006-downloads-community-and-feature-admin.md): resolve policy-sensitive features and feature-owned administration
- [`007-search-home-sitemap-and-cross-feature-reads.md`](007-search-home-sitemap-and-cross-feature-reads.md): migrate cross-feature read models and remaining aggregators
- [`008-public-document-root-and-routing.md`](008-public-document-root-and-routing.md): activate the front controller and public-only web root
- [`009-database-tests-and-deployment-hardening.md`](009-database-tests-and-deployment-hardening.md): harden migrations, tests, packaging, and deployment
- [`010-legacy-removal-and-architecture-signoff.md`](010-legacy-removal-and-architecture-signoff.md): remove compatibility code and approve the implemented architecture

## Standard Status Values

- `Not started`
- `Foundation implemented` - supporting code exists, but the phase gate is not satisfied
- `In progress`
- `Blocked`
- `In review`
- `Complete with evidence` - acceptance criteria and validation evidence are recorded

## Program Definition of Done

The migration is complete when every legacy route contract is either preserved or deliberately superseded by an approved compatibility decision, only `public/` is web-accessible, all application code uses the documented boundaries, private runtime data remains external, the full verification suite passes from a clean deployment, and no runtime code depends on the old procedural include layer.
