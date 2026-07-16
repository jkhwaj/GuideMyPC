# Task: Structured Repair Guides

- Status: Completed
- Priority: Critical
- Release: R1
- Dependencies: `003-database-migrations-and-seeds.md`, `005-responsive-design-system-and-layout.md`, `008-knowledge-base.md`

## Objective

Deliver safe, understandable repair guides with enough context for nontechnical users to complete and verify each step.

## Current State

`guides` stores descriptive metadata and a freeform `content` field while `guide_steps` stores basic ordered text, causing duplication. Existing pages support progress and ratings but lack required tools, rich warnings, media, verification, next steps, and print presentation.

## Scope

- Define one authoritative structured guide model.
- Include platform/version, difficulty, estimated time, required tools, prerequisites, risk level, backup warning, and last-reviewed date.
- Model ordered steps with title, instruction, expected result, warning, image, and optional video timestamp.
- Embed approved YouTube videos using privacy-enhanced mode and an accessible fallback link.
- Add progress checklist, completion summary, troubleshooting branches, next actions, related content, and AI/diagnostic escalation.
- Add printer-friendly output that preserves warnings and source URLs.
- Retain favorites and ratings only after their security and validation are corrected.

## Non-Goals

- Animation production
- Hosting copied third-party videos
- Arbitrary HTML or executable code in instructions
- Claiming guaranteed repair outcomes

## Implementation Steps

1. Migrate existing content to a single structured guide representation without data loss.
2. Add tools, warnings, media, expected-result, source, and review metadata.
3. Build guide summary, step, checklist, completion, related-help, and print components.
4. Persist checklist state for users and keep a temporary session state for guests.
5. Add clear sign-in prompts without discarding guest progress.
6. Add editorial validation for dangerous actions, external commands, and stale platform versions.
7. Seed representative safe guides across all primary categories.

## Database Changes

- Extend guide and step structures for tools, warnings, expected results, media, publication, review, and ordering.
- Add guide-tool, guide-source, and related-content relationships as needed.
- Add ordering uniqueness and transactional guide-edit behavior.

## Security and Privacy

- Allow only approved media hosts and URL schemes.
- Escape commands and code as text.
- Require explicit warnings for data loss, firmware, registry, account reset, or device disassembly.
- Do not load third-party video frames until user consent if required by privacy policy.

## Accessibility

Use semantic ordered steps, descriptive image alt text, captions/transcripts or equivalent summaries, keyboard-operable checklists, printable contrast, and warnings announced before risky instructions.

## Affected Files

- `guide.php`
- `guides.php`
- `save_progress.php`
- `toggle_favorite.php`
- `rate_guide.php`
- guide-related admin routes
- guide migrations, shared components, CSS, and JavaScript

## Acceptance Criteria

- [x] Every guide displays difficulty, time, tools, warnings, platform, and review date.
- [x] Every step can state the action, expected result, and recovery path.
- [x] Progress works for guests during the session and persists for signed-in users.
- [x] Videos have accessible fallback information and use approved embeds.
- [x] Print output includes all steps and safety warnings without navigation clutter.
- [x] Guide edits are transactional and cannot leave partial step data.
- [x] Existing guide URLs remain valid after migration.

## Validation

- Migrate and compare existing guide content and step order.
- Complete a guide as guest and signed-in user, including reload and print flows.
- Test missing images, blocked video embeds, no-JavaScript behavior, and dangerous-step warnings.
- Run accessibility tests against summary, checklist, video, and print presentations.

Validation completed locally: migration `011_structured_guides.sql` applied to the live local database and the fresh knowledge test database, then both reported eleven migrations with zero new changes on rerun. The safe seed added structured metadata, two ordered steps, tools, warning/recovery/expected-result text, and an official source for the Windows update guide. The public guide route returned HTTP 200 with the structured summary, warning, source, guest checklist, and existing slug; the missing-guide route returned 404; and GET progress returned 405. A browser-session request completed a guest step through the real POST/CSRF/redirect flow and retained it after redirect. Helper tests verify approved media handling; the focused integration test writes structured step/tool data inside a transaction then rolls it back. PHP lint, `git diff --check`, and an unsafe-DOM scan passed. Print CSS hides navigation while retaining step and source content.

## Definition of Done

A beginner can safely understand, follow, verify, save, and revisit a complete repair procedure from one page.
