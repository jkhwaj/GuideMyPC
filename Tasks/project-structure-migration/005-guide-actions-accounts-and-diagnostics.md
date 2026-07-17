# Task: Guide Actions, Accounts, and Diagnostics

- Status: Not started
- Priority: Critical
- Release: M3
- Dependencies: `004-knowledge-and-guide-read-models.md`

## Objective

Migrate stateful guide behavior, account workflows, and the currently working diagnostic flow into feature-owned commands and services without changing session, CSRF, authorization, redirect, or mixed HTML/JSON contracts.

## Current State

Guide progress, favorites, ratings, activity, and guest progress are tightly coupled to account sessions and guide detail. Account pages combine validation, password operations, reset links, profile/settings updates, and database writes. Diagnostics create and transition persistent sessions through separate GET and POST routes.

## Scope

- Extract guide view/activity, progress, favorite, rating, and guest-progress merge commands.
- Extract registration, login, logout, password reset, profile, settings, account request, and account-history behavior.
- Extract the active diagnostic session and transition flow.
- Preserve all documented session keys and regeneration behavior.
- Preserve POST/CSRF/PRG behavior and current mixed HTML/JSON responses.
- Keep password-reset link paths compatible.
- Add feature-specific authorization, validation, and transaction tests.
- Remove migrated guide, account, and diagnostic logic from procedural helper files when no caller remains.

## Non-Goals

- Adding social login or new account capabilities
- Redesigning diagnostic scoring or confidence ranking
- Activating unwired diagnostic resource types
- Changing password or session policy without an approved security decision
- Introducing clean URLs or an API-first account service
- Replacing `mysqli`

## Implementation Steps

1. Characterize every guide action and account/diagnostic route, including wrong-method and failure paths.
2. Extract guide commands with explicit user/guest context and transaction boundaries.
3. Extract authentication and account commands while preserving session regeneration and flash behavior.
4. Extract profile, settings, password-reset, and account-data request workflows.
5. Extract diagnostic start, current state, transition validation, and completion behavior.
6. Centralize resource URL resolution only for resource types proven to work; record unsupported types explicitly.
7. Replace root route implementations with compatibility dispatchers.
8. Make tests create and clean their own users, tokens, progress, activity, and diagnostic sessions.
9. Remove orphan-prone test behavior and fail safely if test database configuration is ambiguous.

## Database Changes

No product schema redesign is intended. Any integrity corrections discovered in account or diagnostic tables require new forward migrations and upgrade tests. Historical migrations remain unchanged.

## Security and Privacy

- Preserve password hashing, reset-token hashing/expiry, session regeneration, CSRF, and authorization behavior.
- Never log passwords, tokens, cookies, diagnostic private content, or account request details.
- Users may access and mutate only their own progress, favorites, profile, settings, diagnostics, and account requests.
- Invalid or tampered diagnostic transitions must fail without disclosing internal graph state.
- JSON errors remain bounded and user-safe.

## Accessibility

- Preserve associated labels, validation summaries, focus movement, status announcements, password guidance, and diagnostic progress semantics.
- Account and diagnostic workflows must remain usable without JavaScript where the existing contract supports it.

## Affected Files

- `app/Features/Guides/`
- `app/Features/Accounts/`
- `app/Features/Diagnostics/`
- corresponding views under `resources/views/`
- guide action, account, profile/settings, and diagnostic root routes
- `includes/accounts.php`
- `includes/guides.php`
- `includes/diagnostics.php`
- account, guide, diagnostic, session, and authorization tests
- password-reset and deployment documentation

## Rollback Strategy

Move one command or workflow at a time behind the existing endpoint. Retain compatibility wrappers until all HTML and JSON callers pass. Roll back a failed workflow to its prior handler without reverting completed read-model extraction.

## Acceptance Criteria

- [ ] Guide actions preserve input names, response mode, statuses, redirects, and session effects.
- [ ] Guest progress and authenticated progress merge exactly once under the documented conditions.
- [ ] Registration, login, logout, reset, profile, settings, and account requests preserve security contracts.
- [ ] Diagnostic sessions reject invalid ownership, state, token, and transition attempts.
- [ ] Existing session keys remain compatible through the migration.
- [ ] Every state-changing route verifies its approved method, CSRF, authentication, and authorization policy.
- [ ] Tests use a dedicated database and clean all data they create.
- [ ] No test can silently target the normal application database.
- [ ] Migrated root scripts contain no feature SQL or rendering.

## Validation

- Test guest, authenticated, cross-user, expired-session, invalid-CSRF, wrong-method, HTML, and JSON paths.
- Verify registration, login, logout, password request/reset, profile/settings, account history, and account-data request behavior.
- Test guide progress, completion, favorites, ratings, activity, and guest merging for duplicate side effects.
- Test diagnostic start, resume, valid transition, invalid transition, completion, and ownership denial.
- Inspect session regeneration and cookie behavior without exposing identifiers in evidence.
- Run isolated database cleanup checks, PHP lint, and the full fast verification suite.

## Definition of Done

Guide mutations, account workflows, and active diagnostics are feature-owned, enforce the shared security boundary, retain legacy endpoint contracts, and have deterministic cross-user and failure-path coverage.
