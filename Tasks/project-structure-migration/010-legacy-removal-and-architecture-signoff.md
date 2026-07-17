# Task: Legacy Removal and Architecture Sign-Off

- Status: Blocked
- Priority: Critical
- Release: M6
- Dependencies: `009-database-tests-and-deployment-hardening.md`

## Objective

Remove the procedural compatibility layer only after all routes, scripts, tests, and deployment paths use the target structure, then synchronize project documentation with the architecture that is actually running.

## Current State

Throughout tasks `001` through `009`, `config.php`, root route scripts, global helper functions, global `$conn`, and selected `includes/` files remain temporary migration seams. They must not become a permanent second architecture.

## Scope

- Find and remove remaining runtime references to procedural application includes.
- Remove the global database connection and obsolete compatibility wrappers.
- Remove root route implementations once the public router owns every route.
- Remove temporary Composer `autoload.files` entries if any were used.
- Delete obsolete layout, helper, feature, and bootstrap files only after proving they have no callers.
- Confirm foundation-only code is either deliberately retained with ownership/status or removed.
- Update architecture, setup, deployment, testing, submission, and contributor documentation.
- Record final route, dependency, security, and deployment evidence.
- Decide whether clean URL work becomes a separate future task; do not add it implicitly here.

## Non-Goals

- A framework rewrite
- Clean URL migration or legacy redirects
- New product features
- Database engine or ORM migration
- Deleting operational history, migration files, or useful decision records
- Removing compatibility behavior without route evidence

## Implementation Steps

1. Search code, tests, scripts, Composer metadata, and documentation for `includes/`, `config.php`, global `$conn`, and deprecated wrappers.
2. Trace every remaining caller and migrate it to the approved boundary.
3. Remove compatibility code in small groups and run focused plus full verification after each group.
4. Remove obsolete root route files only when the public router handles their exact paths.
5. Classify dormant foundations as retained, deferred, or removed and document the decision.
6. Rebuild the code knowledge graph and review dependency direction and high-risk flows.
7. Update `docs/project-structure.md` from transitional to implemented architecture.
8. Synchronize `docs/application-conventions.md`, system overview, diagrams, README, deployment, testing, and packaging documentation.
9. Run the complete release gate from a clean artifact and database.
10. Record final architecture approval and any explicitly accepted residual risks.

## Database Changes

None expected. Legacy code removal must not delete historical migrations or alter schema compatibility.

## Security and Privacy

- Confirm removal does not bypass security wrappers, error redaction, authorization, CSRF, or rate limiting.
- Confirm no source or private runtime path becomes public after deleting old `.htaccess` defenses.
- Remove dead code containing obsolete credential, file, or trust assumptions.
- Preserve audit, retention, and account-data workflows.

## Accessibility

Run the final representative accessibility matrix after legacy presentation code is removed, including errors, forms, diagnostics, search, administrator workflows, desktop, and 320px layouts.

## Affected Files

- `config.php`
- root PHP route files
- obsolete files under `includes/`
- Composer autoload configuration
- application, script, and test callers
- `docs/project-structure.md`
- `docs/application-conventions.md`
- `docs/submission/system-overview.md`
- architecture diagrams
- README, setup, testing, deployment, release, and packaging documentation
- `AGENTS.md` references if paths changed

## Rollback Strategy

Remove compatibility groups in separate reversible changes. Keep the previous deploy artifact and database-compatible release available. If a missing caller is discovered, restore only the required wrapper while adding coverage, then repeat removal.

## Acceptance Criteria

- [ ] No runtime application, script, or test references obsolete `includes/` application code.
- [ ] No route depends on global `$conn` or a root implementation file.
- [ ] No temporary procedural Composer autoload bridge remains.
- [ ] Every legacy URL is handled by the public router according to the approved route contract.
- [ ] Only `public/` is web-accessible and private storage remains external.
- [ ] Core, Security, Feature, view, route, database, and script dependencies match `docs/project-structure.md`.
- [ ] Dormant foundations have explicit retained/deferred/removed status and are not represented as completed features.
- [ ] Architecture diagrams and contributor/deployment documentation match the implemented tree and request flow.
- [ ] The complete release gate passes from a clean artifact and database.
- [ ] Residual risks and any future clean URL proposal are documented separately.

## Validation

- Rebuild the code knowledge graph and inspect imports, callers, high-risk flows, and untested hotspots.
- Search for `includes/`, `config.php`, global `$conn`, deprecated wrapper names, direct root asset paths, and physical-script metadata assumptions.
- Run Composer validation, PHP lint, static analysis if configured, migrations, repeated seeds, unit/integration tests, and the full Apache route matrix.
- Probe all private paths and sensitive extensions.
- Run guest, user, administrator, HTML, JSON, XML, error, and CLI workflows from a clean deployment artifact.
- Compare the final filesystem and request flow with all architecture diagrams and documentation.

## Definition of Done

GuideMyPC runs solely through the approved feature-oriented structure and public document root, all temporary procedural seams are removed, the full release evidence passes, and project documentation accurately describes the implemented system.

## Initial Sign-Off Audit

This task was audited after the first task `009` hardening increment. Legacy removal must not begin yet.

- `config.php` remains the compatibility facade and creates the global `$conn` connection for root routes.
- Root page and action scripts still load `config.php` and/or feature files under `includes/`; only the About page is routed through the new view boundary.
- `bootstrap/web.php` and `bootstrap/cli.php` still compose the procedural bootstrap, function, security, error, and database includes while their namespaced replacements are extracted incrementally.
- The repository root remains the documented Apache document root. Its protection relies in part on rewrite-dependent `.htaccess` rules, and no public front controller or legacy route map is active.
- The temporary PSR-4 fallback remains necessary until Composer is installed and the approved deploy artifact workflow is verified.
- Task `006` still lacks approved canonical Community and Download eligibility decisions; task `007` aggregators depend on those policies; task `008` has not migrated assets or activated public-root routing.
- The full integration suite now refuses to run without an explicit `DB_TEST_NAME`; a migrated and seeded dedicated database must be configured before final release evidence can pass.
- The code graph still has a low-cohesion `includes-require` community and reports untested hotspots in legacy Search, Security, Diagnostics, database, and error-handling paths.

## Removal Exit Gates

Do not change this task to `In progress` until all of the following are true:

1. Tasks `006` through `009` have completed their policy, routing, and release gates.
2. Every legacy route is served through the public router and covered by the route-contract matrix.
3. Public assets have moved under `public/`, and Apache/Nginx private-path probes pass without relying on repository-root rewrite protection.
4. All root route, script, and test callers have migrated from `config.php`, global `$conn`, and procedural `includes/` dependencies.
5. Composer is installed or otherwise available in the documented build workflow, and the locked production artifact is validated from a clean extraction.
6. Fresh migration, representative upgrade, repeated seed, isolated integration suite, HTTP route matrix, and backup/restore checks pass with recorded evidence.
