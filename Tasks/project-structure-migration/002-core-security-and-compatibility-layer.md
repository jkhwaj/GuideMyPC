# Task: Core, Security, and Compatibility Layer

- Status: Not started
- Priority: Critical
- Release: M1
- Dependencies: `001-composer-and-bootstrap-boundaries.md`

## Objective

Extract shared runtime and security responsibilities into namespaced `Core` and `Security` code while preserving the existing global function, session, redirect, error, and database contracts through temporary wrappers.

## Current State

Environment loading, filesystem setup, errors, sessions, headers, utility functions, authorization, CSRF, rate limiting, response negotiation, and database creation are tightly coupled under `includes/`. Most routes use global functions, superglobals, and a global `$conn`.

## Scope

- Extract configuration, environment, URL, request, response, database, transaction, logging, and error responsibilities into `app/Core/`.
- Extract sessions, authentication, authorization, CSRF, rate limiting, HTTPS policy, and security headers into `app/Security/`.
- Preserve current global helper names as thin compatibility wrappers.
- Preserve the current session keys and flash/old-input behavior.
- Preserve HTML and JSON error negotiation, request IDs, special status `419`, and HTTP 303 redirects.
- Retain `mysqli` and current SQL semantics.
- Keep private logs and rate-limit files under configurable external storage.
- Remove unconditional database creation from the shared request path.

## Non-Goals

- Removing all global functions or `$conn`
- Migrating feature-specific SQL
- Adding middleware infrastructure for its own sake
- Applying CSRF indiscriminately to every POST endpoint
- Enforcing a new CSP policy
- Changing cookie, proxy, publication, or authorization policy without an approved decision
- Introducing an ORM or container

## Implementation Steps

1. Map each existing shared function to its target responsibility and known callers.
2. Extract configuration and external private-path resolution first.
3. Extract logging and error handling while retaining redaction and request correlation.
4. Extract request/response behavior, including HTML/JSON negotiation and safe aborts.
5. Extract the `mysqli` connection factory and transaction behavior.
6. Extract session setup using the approved HTTPS and trusted-proxy policy.
7. Extract CSRF, authentication, authorization, redirect, and rate-limit behavior.
8. Replace old function bodies with delegating wrappers that preserve signatures and return values.
9. Update web, CLI, and test bootstraps to compose only their required services.
10. Characterize direct global `$conn` access and publish the per-feature removal sequence.

## Database Changes

None. Connection setup and transaction boundaries may change internally, but queries, schema, credentials, and result contracts remain unchanged.

## Security and Privacy

- Preserve secure, HTTP-only, SameSite session cookie behavior under supported HTTP, HTTPS, hostname, and subdirectory configurations.
- Trust proxy headers only from explicitly configured proxy addresses.
- Keep CSRF token names, validation behavior, and `419` responses stable.
- Preserve the intentional contract of any endpoint that does not currently use CSRF until task `000` authorizes a change.
- Logs must redact credentials, cookies, tokens, request bodies, private uploads, and SQL internals.
- Rate-limit storage must remain unavailable through the web server.

## Accessibility

Shared abort and error responses must retain semantic titles, headings, recovery links, keyboard focus visibility, and user-safe messages.

## Affected Files

- new classes under `app/Core/`
- new classes under `app/Security/`
- `bootstrap/web.php`
- `bootstrap/cli.php`
- `bootstrap/test.php`
- `config.php`
- `includes/bootstrap.php`
- `includes/functions.php`
- `includes/security.php`
- `includes/errors.php`
- `includes/db.php`
- helper and security tests
- `docs/project-structure.md`

## Rollback Strategy

Extract one responsibility at a time behind the existing function signature. Each wrapper can return to its prior implementation independently. Do not delete old code until the extracted implementation passes its focused contract tests.

## Acceptance Criteria

- [ ] Core and Security dependencies follow `docs/project-structure.md`.
- [ ] Existing routes can still use all documented global compatibility functions.
- [ ] Static pages do not require a database connection.
- [ ] HTML and JSON success/error formats, request IDs, statuses, and redirects remain compatible.
- [ ] Existing session keys, flash values, old input, CSRF tokens, and guest progress remain compatible.
- [ ] Authentication and administrator authorization outcomes match the route matrix.
- [ ] CLI and test bootstraps do not start sessions or send headers.
- [ ] Logs, sessions, caches, uploads, and rate-limit files remain outside public access.
- [ ] No feature SQL or product behavior is moved incidentally.
- [ ] Compatibility wrappers are marked with their removal task and have direct tests where practical.

## Validation

- Run unit tests for configuration, URL generation, requests, responses, CSRF, redirects, and rate limiting.
- Run the existing integration suite against an isolated migrated and seeded test database.
- Exercise representative HTML and JSON success, validation, authentication, authorization, CSRF, rate-limit, not-found, and unexpected-error paths.
- Verify static routes with MariaDB stopped.
- Verify session cookies on HTTP, HTTPS, hostname, and `/GuideMyPC/` subdirectory configurations.
- Confirm private files remain inaccessible and logs contain correlation data without secrets.
- Compare all results with the route contract from task `000`.

## Definition of Done

Shared runtime and security behavior is implemented behind clear namespaced boundaries, legacy callers remain compatible, and feature routes can migrate without duplicating bootstrap, database, error, or security logic.
