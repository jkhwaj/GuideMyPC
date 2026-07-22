# GuideMyPC Final Report Outline

This reviewed Markdown source was used for the ignored
`GuideMyPC-Final-Report.docx`. The 28-page Hebrew report includes contents,
numbered sections, page numbers, four UML figures, and ten screenshots. Its
release-commit placeholder must be replaced after the authorized final commit.

## Front Matter

- Project: GuideMyPC
- Submitted release commit: `[record final commit]`
- Submission date: `[record date]`
- Group: `[record group name]`
- Repository: `https://github.com/jkhwaj/GuideMyPC`

## 1. Team Roles and Contributions

Complete identity and contact fields only in the private `Readme.docx` and final
report when course policy requires them. Use the concrete contribution
categories in `docs/team/README.md`, reconcile claims with Git and delivered
artifacts, and do not infer work from commit counts alone.

## 2. System Description (Maximum Two Pages)

Adapt `system-overview.md` into concise prose covering:

- The consumer-technology support problem and its safety implications.
- The beginner-to-advanced audience.
- Implemented release capabilities only: published Guides and progress,
  public Knowledge, approved Downloads, Search, accounts, Diagnostics,
  role-aware Dashboard KPI/chart projections, canonical legacy Community, and
  the administration workflows present in the final route inventory.
- Root legacy `*.php` routes as the canonical release interface.
- Explicit exclusions: AI Assistant, Uploads, Maintenance Center, Knowledge
  administration, product Reports, full-resource APIs, Donate, Community v2,
  and unproven mail or CSV behavior.

## 3. Internet Technologies

### Architecture

Include a block diagram limited to the retained scope: browser, local Apache,
canonical root `*.php` routes, PHP bootstrap/security/retained feature layers,
MariaDB, server-rendered HTML, and only `search_suggestions.php` plus
`search_event.php` as bounded JSON responses. Do not depict a REST API,
full-resource API, clean URLs, production hosting, framework, SPA, ORM, or
template engine. Explain that incremental server-rendered PHP preserves the
verified application and avoids an unsupported rewrite.

### Frontend

Document server-rendered HTML, first-party responsive CSS, progressive vanilla
JavaScript, forms, and flash messages as used by retained routes. Record the
final keyboard, accessibility, browser, and responsive checks only after Phase
6 evidence exists; current sources do not prove final accessibility.

### Backend

Document PHP 8.2 canonical legacy routes, shared bootstrap, sessions, CSRF,
authorization, validation, prepared statements, transactions, and redacted
error handling only where verified for retained behavior. Use canonical
Community mutations or another retained, tested workflow as the audited
mutation example. Describe `search_suggestions.php` and `search_event.php` as
two narrow endpoints, never as a general API.

### Database

Include a concise ER view derived from migrations and limited to data needed by
public Knowledge, Dashboard projections, canonical legacy Community,
Diagnostics, Search suggestion/event behavior, and supporting users/roles.
Historical tables for excluded or dormant features may be identified only as
schema history, not as implemented product capability. Do not infer lifecycle
states or relationships that are not verified in the release schema and code.

### Platform

Use exact versions from `readiness-baseline.md` and
`third-party-inventory.md`. The release environment records Windows 10 Pro
25H2 build 26200 and exact PHP, Apache, MariaDB, Composer, browser, Word, and
Visual Paradigm versions. State that this is local Windows/XAMPP evidence and
does not prove production hosting.

## 4. Screenshots

Embed exactly 8-10 numbered screenshots from the retained scope in
`screenshots/README.md`. Use canonical root `*.php` routes and record role,
viewport, caption, alt text, capture date, release commit, and redactions.
Include at least two mobile views and one safely handled boundary/error state.
The ten ignored images listed in `screenshots/README.md` were captured and
reviewed on 2026-07-23. The final package manifest supplies release-commit and
per-file hash binding.

## 5. UML Diagrams

Create and export the following diagrams from `GuideMyPC.vpp`:

1. Use Case: guest, authenticated user, editor, and administrator actions only
   across retained Guides, Knowledge, Downloads, Search, account/progress,
   Diagnostics, Dashboard, canonical Community, and documented administration.
2. Domain Class: verified schema relationships required by retained users,
   categories, Guides/steps/progress, Knowledge, approved Downloads,
   Diagnostics, canonical Community, Search telemetry, and audit behavior, not
   invented PHP classes or excluded-feature tables presented as active.
3. Activity: the retained Diagnostic start-to-result path, including only
   validation, branching, backtracking, uncertainty, and escalation behavior
   demonstrated by code and tests.
4. State Machine: a verified lifecycle from Diagnostics or canonical legacy
   Community, with only release-supported states and transitions.

All four native diagrams exist in the ignored `GuideMyPC.vpp`; four readable
PNG exports and report captions were inspected. `artifact-evidence.md` records
the source hash, review, and scope boundary.

## 6. Git and GitHub Evidence

Record the repository URL, submitted commit, branching model, and real checks.
The readiness branch started from merge commit `8dd43bc` (GitHub pull request
#2); do not infer reviewer approval, issue tracking, or CI from that merge.
The authorized final readiness commit and exact package SHA remain pending.
Include only screenshots or exports that do not disclose private account
information.

## 7. Third-Party Inventory

Use `third-party-inventory.md`. Keep first-party assets separate from
third-party software. Reconcile the final table with runtime versions,
committed assets, external service integrations, and the exact Word and Visual
Paradigm versions actually used after those artifacts are created.

## 8. Appendices

Include only material supporting the retained scope: Phase 2 cleanup evidence,
final test evidence when available, known limitations, security/privacy notes,
selected source references, and final archive evidence. Identify final evidence
by release commit and date; do not relabel readiness checks as final sign-off.
