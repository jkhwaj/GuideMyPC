# Task: Maintenance Center

- Status: Not started
- Priority: Medium
- Release: R2
- Dependencies: `008-knowledge-base.md`, `009-structured-repair-guides.md`, `010-authentication-and-user-profile.md`

## Objective

Give users a practical daily, weekly, and monthly maintenance checklist linked to reviewed GuideMyPC instructions.

## Current State

Maintenance advice is part of the product requirements but has no dedicated page, schedule model, personalization, completion tracking, or homepage content source.

## Scope

- Publish daily, weekly, monthly, quarterly, and event-triggered maintenance recommendations.
- Cover operating-system updates, driver guidance, storage cleanup, security scans, passwords, backups, SSD health, battery health, and Wi-Fi optimization.
- Filter recommendations by platform/device profile without automatic device detection.
- Link every action to a reviewed guide or knowledge article.
- Allow users to mark tasks complete, snooze optional tasks, and view recent completion history.
- Provide guest checklists for the current session and persistent account history.
- Surface a small curated set on the homepage.

## Non-Goals

- Background agents on user devices
- Push notifications in MVP
- Automatic driver installation
- Universal advice that ignores platform/vendor differences

## Implementation Steps

1. Define maintenance recommendation, schedule, platform, resource, and completion structures.
2. Build public overview and platform-filtered views.
3. Add completion/snooze behavior through secure POST endpoints.
4. Add account history and homepage recommendation integration.
5. Add content review/freshness controls in admin.
6. Seed conservative recommendations for each supported platform.

## Database Changes

- Add maintenance recommendations, schedule/platform mappings, and resource links.
- Add per-user completion/snooze records with unique constraints.
- Reuse publication/review conventions from the knowledge base.

## Security and Privacy

Do not claim to know device state unless the user supplies it. Never ask users to upload passwords, license keys, or backup contents. External tools must pass trusted-download review.

## Accessibility

Checklists need clear labels, schedule text, status announcements, and non-color completion indicators. Calendar-like views must also provide linear lists.

## Affected Files

- new maintenance public routes and secure action endpoints
- homepage recommendation section
- profile history section
- database migrations/seeds
- admin content controls

## Acceptance Criteria

- [ ] Users can browse recommendations by cadence and platform.
- [ ] Every recommendation links to reviewed instructions.
- [ ] Completion and snooze work for accounts; session completion works for guests.
- [ ] Advice distinguishes optional optimization from important security/backup work.
- [ ] Homepage recommendations are curated and have safe empty states.
- [ ] Stale recommendations are identifiable to editors.

## Validation

- Test each cadence and supported platform with seeded content.
- Test completion, duplicate submission, snooze expiry, guest state, and cross-user authorization.
- Review seeded advice for unsafe or vendor-specific assumptions.
- Run keyboard and screen-reader checks on checklist interactions.

## Definition of Done

Users can maintain their devices proactively through conservative, reviewed, trackable actions rather than generic cleanup claims.
