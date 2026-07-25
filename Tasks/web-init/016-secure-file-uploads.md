# Task: Secure File Uploads

- Status: Not started
- Priority: Critical
- Release: R3
- Dependencies: `002-security-bootstrap.md`, `010-authentication-and-user-profile.md`, `015-ai-assistant.md`

> **Final-release scope notice (2026-07-22):** The user-upload feature is wholly deferred and excluded from the final release. References elsewhere to private storage, upload-path denial, or package exclusions are security hygiene, not proof of an Uploads capability. The task body remains historical planning; apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Allow authorized screenshot and text-log uploads for AI and community troubleshooting without creating a public file-hosting or code-execution risk.

## Current State

An empty `uploads/` directory exists under the Apache document root. There is no upload endpoint, content inspection, quota, storage policy, access authorization, image processing, retention, or deletion workflow.

## Scope

- Support a small approved set of screenshots and plain-text logs.
- Validate size, actual MIME/content signature, dimensions, extension, and ownership server-side.
- Re-encode accepted images to safe formats and strip metadata where supported.
- Store randomized names outside public access and serve through authorized endpoints.
- Scan or quarantine files before use; define a local-development fallback and production scanner requirement.
- Add per-user/per-session quotas, rate limits, retention, deletion, and moderation status.
- Require explicit user confirmation before sending an upload to an AI provider.
- Show warnings about passwords, license keys, email addresses, serial numbers, IP addresses, and personal images.

## Non-Goals

- Executables, archives, office documents, packet captures, or arbitrary binary logs
- Permanent public image hosting
- Browser-trusted MIME validation
- OCR as an authorization or malware control

## Implementation Steps

1. Define allowed formats, byte/dimension limits, quotas, retention, and lifecycle states.
2. Move storage out of the public web path or deny it at Apache level.
3. Build upload, status, authorized view, and delete endpoints.
4. Add image re-encoding/metadata stripping and text-log normalization.
5. Integrate scanner/quarantine and prevent use before approval.
6. Add explicit AI-share consent and community attachment permissions.
7. Add scheduled cleanup for expired, rejected, and orphaned files.

## Database Changes

- Add upload metadata, owner/session, purpose, original display name, server name, MIME, size, hash, status, consent, retention, and timestamps.
- Index ownership, status, hash, and expiry.

## Security and Privacy

- Never execute or directly serve uploaded files.
- Reject double extensions, polyglots where detectable, oversized dimensions, active image formats, path traversal, and null-byte tricks.
- Enforce ownership on every view/delete/AI-use request.
- Use `Content-Disposition`, `X-Content-Type-Options`, and a restrictive content policy on delivery.
- Document provider retention when files are sent to AI.

## Accessibility

Upload controls need labels, accepted-format/size guidance, progress/status text, accessible previews with user-provided descriptions where needed, and clear remediation for rejected files.

## Affected Files

- `uploads/` access configuration or replacement private-storage path
- new upload service and endpoints
- upload migrations
- AI and community attachment integrations
- cleanup job and documentation

## Acceptance Criteria

- [ ] Only approved image and plain-text formats pass server-side validation.
- [ ] Uploaded files cannot be addressed directly under Apache.
- [ ] Cross-user view, delete, and AI-use attempts are denied.
- [ ] Quarantined/unscanned files are never shown or sent to AI.
- [ ] Images are re-encoded and metadata is removed where supported.
- [ ] Users can delete uploads and understand retention/provider sharing.
- [ ] Expired and orphaned files are cleaned up automatically.

## Validation

- Test valid screenshots/logs and renamed executables, SVG, HTML, PHP, polyglot samples, double extensions, oversized files/images, traversal names, and duplicate uploads.
- Attempt direct HTTP and cross-user access.
- Simulate scanner failure and cleanup jobs.
- Verify AI receives a file only after explicit consent.

## Definition of Done

Uploads provide troubleshooting value while remaining private, bounded, inspected, revocable, and non-executable.
