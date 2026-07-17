# Task: Admin and Content Moderation

- Status: In progress
- Priority: High
- Release: R4
- Dependencies: `008-knowledge-base.md` through `017-community-forum.md`

## Objective

Consolidate content operations into a secure administration area with publishing workflows, moderation queues, auditability, and safe previews.

## Current State

The prototype has separate admin pages for categories, guides, downloads, users, comments, and community content. The structured-guide workflow is the representative implemented foundation: create, update, and delete use server-side admin checks, POST/CSRF protection, validation, transactions, and redacted audit records. Other admin routes still have duplicated patterns, and shared queues, editorial states, pagination/filtering, and broader moderation remain incomplete.

## Scope

- Create a consistent admin shell with role-aware navigation and dashboard queues.
- Manage categories, guides/steps, knowledge articles, sources, videos, diagnostics, maintenance, downloads, users, AI safety content, and community reports.
- Support draft, review, publish, archive, restore, lock, and reject actions appropriate to each type.
- Add server-side validation, safe preview, pagination, filtering, and bulk actions with confirmation.
- Add an immutable audit trail for security, publication, moderation, and role changes.
- Distinguish administrator, editor, and moderator capabilities if operational staffing requires it.
- Add stale-content and broken-resource review queues.
- Build reusable list/table, search, filter, sort, pagination, empty-state, error, confirmation, flash/toast, and bulk-action patterns.
- Add an operational dashboard for pending reviews, user reports, stale content, broken links, failed jobs, and moderation backlog without exposing private content.

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
8. Apply shared list and feedback components to representative content, user, download, diagnostic, and moderation screens before removing one-off implementations.
9. Add aggregate operational metrics and drill-down links to their owning queues.

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

Admin tables require captions/headers and responsive alternatives; forms require labels and error summaries; confirmations are keyboard accessible; queue state and audit changes are understandable without color. Flash/toast messages must be announced appropriately and remain available after server redirects without depending only on JavaScript.

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
- [ ] Representative admin screens use the shared list, filtering, pagination, confirmation, and feedback patterns.
- [ ] The operational dashboard reports current queue/backlog counts and links to filtered records without leaking sensitive data.

## Validation

- Guide CRUD foundation: `tests/guide_integration_test.php` validates guide/category checks, transactional step/tool updates, and redacted audit metadata; `scripts/verify.php` runs it with the integration suite.
- Test each capability as guest, user, moderator/editor if present, and admin.
- Attempt direct requests to hidden controls and obsolete endpoints.
- Verify audit records for create/edit/publish/delete/restore/role/moderation actions.
- Test pagination, filters, validation, concurrent edits, and transaction rollback.
- Run keyboard and screen-reader checks on representative admin workflows.
- Compare dashboard counts with direct queue queries and verify empty, stale, failed-job, and high-volume states.

## Definition of Done

Authorized staff can safely publish and moderate the complete MVP without direct database editing or unaudited privileged actions.
