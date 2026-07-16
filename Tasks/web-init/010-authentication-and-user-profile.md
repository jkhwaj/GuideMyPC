# Task: Authentication and User Profile

- Status: Not started
- Priority: High
- Release: R2
- Dependencies: `002-security-bootstrap.md`, `003-database-migrations-and-seeds.md`, `005-responsive-design-system-and-layout.md`

## Objective

Provide secure guest and free-account experiences with saved troubleshooting activity and understandable account controls.

## Current State

Registration, login, logout, roles, sessions, profile, favorites, guide ratings, and progress exist in basic form. Session hardening, CSRF, password reset, activity history, and consistent authorization are incomplete.

## Scope

- Harden registration, login, logout, and role checks through task `002` controls.
- Validate normalized email, password length, and display name server-side.
- Add email-based password reset with expiring single-use tokens when mail configuration is available.
- Add favorites/bookmarks, recently viewed content, search/diagnostic history, and saved progress.
- Add account settings, session logout, profile deletion/export request path, and privacy explanation.
- Preserve useful guest state when a visitor creates an account.
- Prepare provider-neutral OAuth account linking without requiring OAuth for MVP launch.

## Non-Goals

- Paid account levels
- Public social profiles
- Mandatory OAuth
- Reputation system

## Implementation Steps

1. Refactor existing account routes onto shared security and validation helpers.
2. Add password reset request/consume flows with non-enumerating responses.
3. Add profile sections for saved guides, progress, diagnostic sessions, and recent activity.
4. Define retention and deletion behavior for activity history.
5. Merge guest guide/diagnostic state after explicit login or registration.
6. Add account event audit records for security-relevant actions.

## Database Changes

- Add password reset tokens stored as hashes with expiry and use timestamps.
- Add user activity/history records with bounded retention.
- Add account security event records.
- Extend users only with necessary profile and status fields.

## Security and Privacy

Use `password_hash`/`password_verify`, generic login/reset responses, token hashing, rate limits, session regeneration, and verified authorization. Provide clear retention and deletion rules. Never expose one user's history to another.

## Accessibility

Forms need labels, autocomplete attributes, clear password requirements, announced errors, logical focus, and no CAPTCHA that lacks an accessible alternative.

## Affected Files

- `login.php`
- `register.php`
- `logout.php`
- `profile.php`
- favorites/progress endpoints
- new reset/settings/account-action routes
- account migrations and email templates

## Acceptance Criteria

- [ ] Guest, user, and admin access boundaries are consistently enforced.
- [ ] Authentication resists session fixation, CSRF, enumeration, and basic brute force.
- [ ] Users can revisit favorites, history, progress, and saved diagnostics.
- [ ] Password reset tokens expire, are single use, and are stored hashed.
- [ ] Users can understand and request deletion/export of stored personal activity.
- [ ] Guest progress can be retained when creating an account without overwriting newer user data.

## Validation

- Test valid/invalid login, duplicate registration, reset expiry/reuse, session regeneration, role boundaries, and rate limits.
- Attempt cross-user access to every personalized record.
- Test guest-to-account state merge and account deletion/export paths.
- Run keyboard and screen-reader form checks.

## Definition of Done

Accounts add safe, understandable persistence without restricting core public support content behind login.
