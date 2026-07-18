# Task: Composer and Bootstrap Boundaries

- Status: In progress
- Priority: Critical
- Release: M0
- Dependencies: `000-architecture-contract-and-route-inventory.md`

## Objective

Introduce Composer autoloading for new classes and separate web, CLI, and test initialization while retaining the existing route implementations and procedural compatibility layer.

## Current State

Root routes load `config.php`, which requires procedural bootstrap, helper, security, error, and database files before opening a global `mysqli` connection. CLI scripts and tests reuse portions of the same path, so session startup and database selection are not cleanly isolated. The project has no Composer manifest or namespace convention.

## Scope

- Add a minimal Composer manifest supporting PHP 8.2 and PSR-4 for `GuideMyPC\` under `app/`.
- Define `bootstrap/web.php`, `bootstrap/cli.php`, and `bootstrap/test.php` responsibilities.
- Keep `config.php` as a temporary compatibility facade.
- Ensure CLI and test initialization does not start a browser session or emit web headers.
- Make the normal database connection lazy or explicitly requested rather than required by static pages.
- Define configuration loading order and environment override behavior.
- Implement the Composer deployment decision approved in task `000`.
- Add deterministic lint and Composer validation commands.

## Non-Goals

- Rewriting global functions as classes
- Moving route implementations
- Introducing a dependency injection container
- Installing a framework, ORM, or template engine
- Replacing the manual environment parser unless separately justified and tested
- Changing route, session, or response behavior

## Implementation Steps

1. Add `composer.json` with project metadata, PHP 8.2 requirements, and PSR-4 mapping.
2. Generate and commit `composer.lock` using the approved dependency policy.
3. Add the three bootstrap entry points with explicit web, CLI, and test responsibilities.
4. Load `vendor/autoload.php` from one documented location.
5. Keep existing procedural includes available through `config.php` while avoiding duplicate initialization.
6. Separate configuration loading from session, headers, and database connection creation.
7. Update operational scripts to use the CLI bootstrap where safe.
8. Update tests to use the test bootstrap and require an explicit test database configuration.
9. Update packaging and setup documentation for the selected `vendor/` strategy.
10. Add one verification command that validates Composer and lints tracked PHP files.

## Database Changes

None. This task changes connection initialization, not schema or SQL behavior.

## Security and Privacy

- Production dependency installation must not expose Composer credentials or development packages.
- Test bootstrap must reject ambiguous or normal application database names.
- Environment values must not be logged or committed.
- Web-only security headers and cookies must never be emitted by CLI commands.

## Accessibility

No intended presentation changes. Existing HTML and error responses must remain unchanged.

## Affected Files

- `composer.json`
- `composer.lock`
- `bootstrap/web.php`
- `bootstrap/cli.php`
- `bootstrap/test.php`
- `config.php`
- selected files under `includes/`
- operational files under `scripts/` and `database/`
- tests and setup/deployment documentation
- packaging scripts

## Rollback Strategy

Keep the previous include path usable until every entry point passes validation. If autoload or deployment installation fails, revert entry points to the compatibility facade without changing route files or schema.

## Acceptance Criteria

- [ ] `composer validate` succeeds.
- [ ] A clean dependency installation is deterministic from `composer.lock`.
- [ ] New classes under `app/` autoload through `GuideMyPC\` without manual `require_once` calls.
- [ ] Existing global helpers remain available during migration.
- [ ] Web routes initialize sessions, headers, errors, and request context once.
- [ ] CLI commands initialize no browser session and emit no web headers.
- [ ] Test initialization cannot select the normal configured database accidentally.
- [ ] Static pages can initialize without opening a MariaDB connection.
- [ ] The source/deployment package either contains production dependencies or documents and verifies installation after extraction.
- [ ] Existing route-contract checks remain unchanged.

## Validation

- Run `composer validate` and a clean `composer install` using the documented development workflow.
- Run PHP syntax checks across all tracked PHP files.
- Run the existing helper and integration tests against the dedicated test database.
- Invoke representative database, retention, setup, and account-administration CLI scripts and confirm no session side effects.
- Stop MariaDB and verify static/legal routes still render while database-backed routes fail through the existing safe error contract.
- Build or extract the deployment artifact and verify `vendor/autoload.php` is available by the documented method.

## Definition of Done

All entry-point types have explicit initialization boundaries, new classes can be autoloaded, existing routes remain compatible, and task `002` can extract shared behavior without relying on one universal procedural bootstrap.

## Implementation Evidence

- Added `composer.json` with the PHP 8.2 requirement and `GuideMyPC\` PSR-4 mapping. Composer and PHP are not available on the current command path, so `composer.lock`, installation validation, and autoload verification remain blocked pending local tooling.
- Added `bootstrap/web.php`, `bootstrap/cli.php`, and `bootstrap/test.php`. They load shared legacy helpers without opening a database connection; only the web bootstrap starts a browser session and sends security headers.
- `config.php` remains the compatibility facade that loads the web bootstrap and opens the legacy global `$conn` for unmigrated routes.
- Static/legal routes now use the web bootstrap directly and therefore do not require MariaDB to initialize.
- `database/runner.php` and the helper test use the CLI/test bootstraps, preventing direct CLI bootstrap callers from starting browser sessions.
