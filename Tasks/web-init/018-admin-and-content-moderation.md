# Task: Admin and Content Moderation

- Status: Not started
- Priority: High
- Release: R4
- Dependencies: `008-knowledge-base.md` through `017-community-forum.md`

## Objective

Consolidate content operations into a secure administration area with publishing workflows, moderation queues, auditability, and safe previews.

## Current State

The prototype has separate admin pages for categories, guides, downloads, users, comments, and community content. Authorization and mutation patterns are duplicated, destructive actions are not consistently protected, and there is no audit trail or multi-state editorial workflow.

## Scope

- Create a consistent admin shell with role-aware navigation and dashboard queues.
- Manage categories, guides/steps, knowledge articles, sources, videos, diagnostics, maintenance, downloads, users, AI safety content, and community reports.
- Support draft, review, publish, archive, restore, lock, and reject actions appropriate to each type.
- Add server-side validation, safe preview, pagination, filtering, and bulk actions with confirmation.
- Add an immutable audit trail for security, publication, moderation, and role changes.
- Distinguish administrator, editor, and moderator capabilities if operational staffing requires it.
- Add stale-content and broken-resource review queues.

## Non-Goals

- Building a generic CMS
- Allowing arbitrary plugin or template code
- Hiding audit actions from administrators
- Complex enterprise approval chains

## Implementation Steps

1. Inventory existing admin routes and map each action to a capability.
2. Introduce centralized admin authorization and common form/list patterns.
3. Refactor existing CRUD operations to POST, CSRF, validation, transactions, and audit events.
4. Add editorial and moderation queues for each owning feature.
5. Add safe preview that uses the public renderer without public publication.
6. Add audit search/export with sensitive-value redaction.
7. Remove or redirect obsolete duplicate admin routes after migration.

## Database Changes

- Add audit events with actor, action, target type/ID, safe metadata, timestamp, and request correlation ID.
- Extend roles/capabilities only if needed; preserve a simple least-privilege model.
- Reuse feature publication and moderation states rather than duplicate tables.

## Security and Privacy

- Enforce authorization on the server for every page and action.
- Re-authenticate or require explicit confirmation for destructive role/account operations.
- Audit both successful high-impact actions and denied attempts where useful.
- Do not store passwords, tokens, raw private uploads, or full sensitive content in audit metadata.

## Accessibility

Admin tables require captions/headers and responsive alternatives; forms require labels and error summaries; confirmations are keyboard accessible; queue state and audit changes are understandable without color.

## Affected Files

- `admin.php`
- all `admin_*`, `add_*`, `edit_*`, and `delete_*` routes
- shared admin layout/authorization/form components
- audit migrations and feature integrations

## Acceptance Criteria

- [ ] Every administrative action has a defined capability and server-side check.
- [ ] All mutations use POST, CSRF, validation, and transactions where needed.
- [ ] Editors can preview drafts without making them publicly searchable.
- [ ] Security-sensitive, publication, role, and moderation actions create redacted audit records.
- [ ] Stale content, broken downloads, reports, and pending reviews have visible queues.
- [ ] Obsolete routes cannot bypass the unified controls.

## Validation

- Test each capability as guest, user, moderator/editor if present, and admin.
- Attempt direct requests to hidden controls and obsolete endpoints.
- Verify audit records for create/edit/publish/delete/restore/role/moderation actions.
- Test pagination, filters, validation, concurrent edits, and transaction rollback.
- Run keyboard and screen-reader checks on representative admin workflows.

## Definition of Done

Authorized staff can safely publish and moderate the complete MVP without direct database editing or unaudited privileged actions.
