# Task: Trusted Downloads

- Status: Not started
- Priority: High
- Release: R3
- Dependencies: `002-security-bootstrap.md`, `003-database-migrations-and-seeds.md`, `005-responsive-design-system-and-layout.md`

> **Final-release scope notice (2026-07-22):** This file is retained as historical planning. Maintenance Center and AI recommendation integrations are excluded and are not required to claim independently verified Downloads behavior. Apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Provide a clearly reviewed catalog of official software and driver destinations without misleading buttons, bundled installers, or unsafe mirrors.

## Current State

The prototype stores a name, description, category, and official URL and offers basic admin CRUD. It has no publisher identity, platform/version compatibility, verification evidence, review date, checksum, or approval workflow.

## Scope

- Store publisher, purpose, supported platforms, license/cost label, official URL, download type, verification date, reviewer, and review notes.
- Link only to official publisher/manufacturer sources unless an exception is explicitly reviewed and disclosed.
- Display prominent “official site” destination text and avoid ad-like or ambiguous controls.
- Support optional checksums and signatures when publishers provide them.
- Add approved, pending review, stale, rejected, and archived states.
- Add periodic link/freshness checks without automatically approving changed destinations.
- Seed reviewed entries such as Microsoft tools, Malwarebytes, Rufus, CPU-Z, CrystalDiskInfo, HWMonitor, MemTest86, Ninite, and official Intel/AMD/NVIDIA/Samsung destinations.
- Integrate downloads into search, guides, diagnostics, maintenance, and AI recommendations.

## Non-Goals

- Hosting third-party installers in MVP
- Scraping download files from vendor sites
- Affiliate links without explicit labels
- Automatically endorsing any user-submitted URL

## Implementation Steps

1. Extend the download model and establish URL/publisher validation rules.
2. Build catalog, category, and detail views with clear external-link behavior.
3. Add editorial review and re-verification workflow.
4. Add a scheduled link checker that records status without following unsafe schemes or private-network targets.
5. Update related-content systems to use approved records only.
6. Publish download-review criteria and a report-problem path.

## Database Changes

- Extend downloads with publisher, platforms, state, verification, reviewer, and source metadata.
- Add optional checksums/signatures and verification events.
- Add indexes for state, category, publisher, and freshness.

## Security and Privacy

- Permit only HTTPS destinations except documented official exceptions.
- Protect link checking against SSRF, redirects to private networks, oversized responses, and unsafe protocols.
- Never proxy or silently start downloads.
- Clearly label affiliate or sponsored relationships.

## Accessibility

Links state the destination and external behavior, status is textual rather than color-only, tables have mobile alternatives, and buttons do not use deceptive styling.

## Affected Files

- `downloads.php`
- `add_download.php`
- `edit_download.php`
- `delete_download.php`
- `admin_downloads.php`
- download migrations, checker, related-content integrations, CSS

## Acceptance Criteria

- [ ] Public listings include only approved, non-archived downloads.
- [ ] Every listing identifies publisher, official destination, platforms, and last verification date.
- [ ] Stale or changed links enter review rather than remaining silently trusted.
- [ ] Unsafe URL schemes and private-network checker targets are rejected.
- [ ] No interface contains misleading or duplicate download buttons.
- [ ] Sponsored or affiliate relationships are explicit and do not alter safety review.

## Validation

- Test valid official URLs, redirects, unsafe schemes, localhost/private IPs, broken links, and changed destinations.
- Verify pending/stale/rejected records cannot be recommended publicly or by AI.
- Review seeded catalog entries manually against official publisher sites.
- Run keyboard and screen-reader checks on catalog/detail views.

## Definition of Done

Users can confidently identify why a tool is recommended and leave GuideMyPC only for a reviewed official download destination.
