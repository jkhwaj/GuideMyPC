# Task: Downloads, Community, and Feature-Owned Administration

- Status: In progress
- Priority: Critical
- Release: M4
- Dependencies: `005-guide-actions-accounts-and-diagnostics.md`; approved Community and Downloads decisions from task `000`

## Objective

Resolve policy-sensitive data boundaries, migrate Downloads and Community using their approved models, and place administrative commands with the features they modify while applying consistent authorization and audit behavior.

## Current State

Public Download routes, homepage/search queries, schema review states, and unused validation helpers disagree on which records are safe to expose. Active Community routes use legacy post/comment/like tables while a newer question/answer schema and helper foundation remain unwired. Administrative CRUD is spread across root scripts, and audit coverage is inconsistent outside guide operations.

## Scope

- Implement the approved public Download eligibility and trusted-URL policy in one feature-owned boundary.
- Migrate public Download listing and relevant metadata.
- Implement the approved canonical Community model without maintaining two competing runtime architectures.
- Migrate active Community list, create, comment/answer, like/vote, reporting, or moderation behavior only to the extent approved in task `000`.
- Move administrative category, guide, download, user, and community commands into their owning features.
- Share administrator authorization and audit recording through Security/Core services.
- Ensure all mutations complete validation and authorization before output.
- Preserve legacy paths and form contracts unless an approved behavior correction explicitly supersedes them.

## Non-Goals

- Automatically activating foundation-only community-v2 capabilities
- Maintaining both Community schemas as equal runtime models
- Building a monolithic `Features/Admin` data layer
- Expanding Download verification beyond the approved product policy
- Changing unrelated guide, account, or search behavior
- Editing historical migrations

## Implementation Steps

1. Publish the approved Download eligibility matrix and Community model decision.
2. Add focused characterization tests that expose current inconsistencies and separate compatibility assertions from approved corrections.
3. Centralize Download publication, review-state, HTTPS, and host validation.
4. Extract Download public queries, views, and administrative commands.
5. Extract only the approved Community model and workflows.
6. Move feature-specific administrator controllers and commands into Guides, Downloads, Accounts, and Community.
7. Apply shared authorization and audit behavior to every administrator mutation.
8. Ensure list/editor routes perform queries and failures before starting output.
9. Replace root scripts with thin compatibility dispatchers.
10. Add corrective migrations only where the approved model requires integrity constraints or safe upgrade behavior.

## Database Changes

New corrective migrations may be required for publication integrity, uniqueness, ownership, audit data, or the selected Community model. Never rename, edit, or reorder applied migration files. Every correction needs fresh-install and existing-schema upgrade tests.

## Security and Privacy

- Public downloads must satisfy the approved publication, review, scheme, and trusted-host rules.
- Server-side validation is required even when forms use browser URL controls.
- Community content must enforce publication, ownership, moderation, and authorization consistently.
- Administrator commands require role authorization, CSRF, validation, safe redirects, and audit records.
- Audit data must identify the action without storing secrets or unnecessary private content.
- User and moderation views must not leak private account data.

## Accessibility

- Preserve accessible tables, filters, status labels, validation summaries, destructive-action warnings, and confirmation flows.
- Publication and moderation states must be conveyed in text, not color alone.

## Affected Files

- `app/Features/Downloads/`
- `app/Features/Community/`
- feature-owned administrator controllers and commands
- corresponding public and administrator views
- Download, Community, category, guide, user, and moderation root routes
- `includes/downloads.php`
- `includes/community.php`
- `includes/admin.php`
- new corrective migrations if approved
- publication, authorization, audit, URL-safety, and moderation tests

## Rollback Strategy

Keep route dispatch reversible per feature. Database corrections require documented forward recovery; do not rely on automatic down migrations. If a new publication policy must be rolled back, restore the previously approved policy explicitly rather than bypassing centralized checks.

## Acceptance Criteria

- [ ] The canonical Community model is documented and only that model drives active feature code.
- [ ] Download public eligibility is defined once and used by public lists, Home, Search, and administration.
- [ ] Unsafe, pending, unpublished, or unapproved downloads behave according to the approved matrix.
- [ ] Community publication and moderation behavior is consistent across its active routes.
- [ ] Administrative commands live with their owning features.
- [ ] Every administrator mutation enforces authorization, CSRF, validation, and audit recording.
- [ ] No administrator page starts rendering before failure-prone processing is complete.
- [ ] Legacy routes and form fields remain compatible except for explicitly approved safety corrections.
- [ ] Corrective migrations pass fresh and upgrade paths without modifying historical checksums.

## Validation

- Test every Download publication/review/scheme/host combination in the approved matrix.
- Test guest, user, moderator/editor, administrator, owner, and cross-user Community outcomes.
- Test create, edit, delete, publish, unpublish, moderation, validation, CSRF, authorization, and audit paths for each administrator workflow.
- Verify direct requests cannot expose ineligible downloads or unpublished Community content.
- Run fresh and representative upgrade migrations when schema corrections exist.
- Run accessibility checks, PHP lint, and the full fast verification suite.

## Definition of Done

Downloads and Community use approved, testable policy boundaries; administration is feature-owned and consistently audited; and no active runtime path depends on competing models or duplicated publication rules.

## Implementation Evidence

- The approved Download eligibility matrix and canonical Community model remain pending decisions from task `000`; public routes and active Community behavior are unchanged.
- Extracted the existing unused Download HTTPS/private-IP/review-state helper behavior into `GuideMyPC\Features\Downloads\DownloadPolicy`, retaining procedural helper delegates for compatibility.
- This extraction does not select or activate a public Download policy: `downloads.php`, Home, Search, and administrator routes still retain their current behavior until the eligibility decision is approved.
- Added helper coverage for the preserved HTTPS, non-HTTPS, approved, and pending behaviors.
- PHP lint passed for the policy, compatibility helpers, public Download route, Community route, and helper test. `tests/helpers_test.php` passes, and a direct autoloaded check confirms private-IP HTTPS URLs remain rejected.
