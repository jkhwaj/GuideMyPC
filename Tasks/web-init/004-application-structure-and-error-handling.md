# Task: Application Structure and Error Handling

- Status: Not started
- Priority: High
- Release: R0
- Dependencies: `002-security-bootstrap.md`, `003-database-migrations-and-seeds.md`

## Objective

Create a small, consistent application foundation that removes duplicated procedural concerns while preserving the current PHP architecture.

## Current State

Pages include `config.php`, layout fragments, and endpoint-specific database code directly. `includes/db.php` and `includes/functions.php` are empty. Error handling commonly terminates with raw database messages, and redirects can depend on output buffering.

## Scope

- Define one early bootstrap for configuration, database connection, sessions, and shared functions.
- Separate page rendering from action endpoints enough to guarantee authorization and redirects happen before output.
- Add centralized validation, old-input, flash-message, pagination, URL, and response helpers.
- Add environment-aware exception handling, generic error pages, and structured file logging.
- Introduce transaction helpers for multi-statement writes.
- Add Composer autoloading and PHPUnit only if adopted by implementation; avoid a framework migration.
- Establish file naming and endpoint conventions for subsequent tasks.

## Non-Goals

- MVC framework adoption
- Complete repository rewrite
- Dependency injection container
- API-first architecture

## Implementation Steps

1. Define the request lifecycle and load bootstrap first on every route.
2. Consolidate database creation with `utf8mb4`, strict errors, and environment-aware failure handling.
3. Populate shared helpers with narrowly reusable behavior.
4. Add 404, 403, 419/invalid-request, 429, and 500 presentation.
5. Introduce application logs outside public access and a request correlation ID.
6. Refactor representative public, account, and admin routes, then apply the pattern throughout.
7. Add a basic automated test harness for pure helpers and database integration.

## Database Changes

No product tables. Database initialization and transactions must use the migration-managed schema.

## Security and Privacy

- Browser errors must never include SQL, file paths, stack traces, secrets, or private content in production.
- Logs must redact credentials, tokens, cookies, and upload contents.
- Correlation IDs must not encode user data.

## Accessibility

Error pages need unique titles, semantic headings, clear recovery links, keyboard-visible focus, and screen-reader-friendly status messages.

## Affected Files

- `config.php`
- `includes/db.php`
- `includes/functions.php`
- new bootstrap, logging, and error-page files
- all root PHP routes as they adopt the lifecycle
- optional `composer.json` and test configuration

## Acceptance Criteria

- [ ] Every route initializes through the same bootstrap before output.
- [ ] Database failures display a generic response and write an actionable redacted log.
- [ ] Multi-query writes use transactions where partial completion would corrupt state.
- [ ] Shared validation and flash messages are used by representative forms.
- [ ] Unknown and unauthorized routes render consistent accessible errors.
- [ ] No framework or unnecessary abstraction is introduced.

## Validation

- Run PHP syntax checks and the initial test suite.
- Trigger controlled 404, 403, validation, and database failures in local mode.
- Verify production-mode responses reveal no diagnostics while logs retain enough context.
- Confirm redirects work with output buffering disabled.

## Definition of Done

Feature tasks can add pages and mutations through documented shared conventions without duplicating bootstrap, security, validation, or error behavior.
