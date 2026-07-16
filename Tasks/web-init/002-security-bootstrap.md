# Task: Security Bootstrap

- Status: Completed
- Priority: Critical
- Release: R0
- Dependencies: `001-xampp-local-setup.md`

## Objective

Establish application-wide security controls before extending public forms, AI, uploads, diagnostics, or community features.

## Current State

Database credentials are hard-coded, sessions start inside the navbar, state-changing actions use GET in several places, CSRF protection is absent, and authorization/validation behavior is duplicated. The SQL dump and configuration live below the public document root.

## Scope

- Load secrets from local environment configuration.
- Start sessions before output with strict, HttpOnly, SameSite cookies and Secure cookies under HTTPS.
- Regenerate the session ID after login and privilege changes.
- Add reusable authentication, admin authorization, CSRF, request-method, escaping, validation, redirect, and flash-message helpers.
- Convert logout, likes, favorites, and destructive admin actions to POST with CSRF tokens.
- Add baseline login/register/posting rate limits and generic authentication errors.
- Protect `.git`, environment files, database assets, task files, logs, and private storage from HTTP access.
- Add security headers suitable for the current site and a Content Security Policy rollout path.
- Enforce prepared statements for all user-influenced queries.
- Remove the legacy public database dump rather than relying on web-server denial rules for its seeded data.
- Keep secrets, logs, backups, database files, and private uploads outside the public document root where possible; Apache deny rules are defense in depth, not the only control.
- Prohibit untrusted values in `innerHTML`; use contextual server escaping, `textContent`, safe DOM construction, or a reviewed constrained renderer.
- Keep setup, migration, health-check, and debug utilities CLI-only or unreachable in production.

## Non-Goals

- AI-specific abuse controls
- Antivirus scanning infrastructure
- Full external penetration testing
- OAuth implementation

## Implementation Steps

1. Introduce an early bootstrap included before all output.
2. Move database settings out of `config.php`; retain no committed secret defaults.
3. Implement request and response security helpers.
4. Inventory every state-changing endpoint and convert it to validated POST.
5. Centralize role checks and remove redirect-after-output behavior.
6. Add access-deny rules and verify that private paths return 403 or 404.
7. Add rate-limit storage suitable for a single-node MVP, with a later Redis migration path.
8. Set production-safe error-display defaults while preserving local diagnostics in logs.
9. Audit browser JavaScript for DOM-based XSS and replace unsafe HTML interpolation.
10. Verify that production cannot serve `phpinfo`, test, setup, migration, or diagnostic scripts.

## Database Changes

- Add rate-limit records only if session/file storage is insufficient.
- Add password-reset and audit structures in their owning tasks, not here.

## Security and Privacy

This task defines the baseline. Use OWASP guidance for sessions, CSRF, password handling, authorization, output encoding, and headers. Avoid logging passwords, CSRF tokens, session IDs, API keys, or uploaded file contents.

- Do not add or depend on the obsolete `X-XSS-Protection` header. Use contextual encoding, CSP, safe DOM construction, and modern headers.
- Never seed or document a shared default administrator password. Create privileged accounts through an explicit local CLI/admin process.
- Failed-login logging is detective evidence, not brute-force prevention; combine it with rate limits, generic errors, secure passwords, session controls, and monitoring.
- Do not rely on `.htaccess` alone to protect sensitive data because overrides can be disabled or hosting behavior can change.

## Accessibility

Security failures must return understandable messages, preserve keyboard focus where appropriate, and not rely on color alone.

## Affected Files

- `config.php`
- `.htaccess`
- `includes/header.php`
- `includes/navbar.php`
- authentication and mutation endpoints in the repository root
- new shared bootstrap/security helpers under `includes/`

## Acceptance Criteria

- [ ] No tracked file contains an active database or provider secret.
- [ ] Private repository paths cannot be downloaded through Apache.
- [ ] Every state-changing request requires POST and a valid CSRF token.
- [ ] Protected routes consistently enforce login and admin roles before output.
- [ ] Login regenerates the session ID and uses hardened cookie settings.
- [ ] User-controlled output is contextually escaped.
- [ ] Basic rate limits cover authentication and public content creation.
- [ ] Private data remains protected even if Apache override rules are unavailable.
- [ ] Client-side rendering does not insert untrusted values through `innerHTML`.
- [ ] No shared default administrator credential or public debug/setup utility exists.
- [ ] Security headers use current controls and do not depend on `X-XSS-Protection`.

## Validation

- Attempt each mutation with GET, missing CSRF, invalid CSRF, logged-out, normal-user, and admin contexts.
- Request `.env`, `.git/config`, `database/guidemypc.sql`, and logs over HTTP.
- Inspect session cookies and response security headers.
- Run PHP syntax checks across all changed PHP files.
- Disable Apache overrides in a representative local check and confirm sensitive data is still outside public reach or otherwise blocked by the web-root design.
- Search PHP and JavaScript for public debug utilities, default credentials, and unsafe HTML insertion.

## Definition of Done

The common security controls are implemented once, used consistently, and verified before feature work proceeds.
