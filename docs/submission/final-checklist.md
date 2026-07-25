# Final Submission Checklist

Complete this checklist against the exact release commit before the internal deadline of 2026-07-30. A reviewer other than the package assembler should sign the final row.

## Release Identity

| Field | Value |
| --- | --- |
| Submitted release commit | `[record commit]` |
| Repository URL | `https://github.com/jkhwaj/GuideMyPC` |
| Internal deadline | 2026-07-30 |
| Formal deadline | 2026-07-31 23:59 |
| Package assembler | `[record name]` |
| Independent reviewer | `[record name]` |
| Sign-off date | `[record date]` |

## Required Deliverables

| Artifact | Required filename | Responsible reviewer | Status | Checked date |
| --- | --- | --- | --- | --- |
| Team Readme | `Readme.docx` | `[private document]` | Created, opened, and ignored; final SHA insertion pending | 2026-07-23 |
| Final report | `GuideMyPC-Final-Report.docx` | `[private document]` | Created, paginated, rendered, and ignored; final SHA insertion pending | 2026-07-23 |
| UML project | `GuideMyPC.vpp` | `[private document]` | Four native diagrams opened and exports inspected | 2026-07-23 |
| Source archive | `GuideMyPC-Source.zip` | `[name]` | Not started |  |

## Content and Evidence

- [x] Phase 3 Markdown sources limit report, UML, and screenshot plans to the verified-core scope.
- [x] Planned URLs use canonical root legacy `*.php` routes and identify only the two bounded Search JSON endpoints.
- [x] The tracked team source contains placeholders instead of member PII.
- [ ] Reconfirm all three Phase 3 checks against the final release commit.
- [x] Report Sections 1-8 are present, numbered, proofread in Hebrew, and use a table of contents and page numbers.
- [x] Team information and contribution claims match the owner-supplied private record and single-project responsibility statement.
- [x] The system description is no more than two pages and describes only the submitted release.
- [x] Technology, database, platform, browser, and 320px claims have traceable evidence.
- [x] Each UML diagram is present in the `.vpp`, legible in export, and matches implemented behavior.
- [x] The report contains exactly ten captioned screenshots, including two mobile views, with manifest entries and redactions.
- [x] Third-party inventory names exact versions, sources, licenses, purposes, and delivery methods.
- [ ] Test evidence records the submitted commit, environment, tester, date, expected/actual result, and safe evidence reference.
- [x] Known limitations distinguish incomplete roadmap foundations from implemented behavior.

## Explicit Scope Guard

- [x] The final report, UML exports, captions, and screenshots make no product
  claim for AI Assistant, Uploads, Maintenance Center, Knowledge
  administration, product Reports, full-resource APIs, Donate, Community v2,
  unproven mail/CSV, clean URLs, framework/SPA/ORM/template-engine adoption, or
  production hosting.
- [x] Published Guides/progress, public Knowledge, approved Downloads, Search,
  accounts, Diagnostics, role-aware Dashboard KPI/charts, canonical legacy
  Community, and documented administration are described only to the extent
  demonstrated by final validation; the two narrow Search JSON endpoints are
  not presented as a resource API.
- [x] Root legacy `*.php` URLs remain canonical throughout the final artifacts.

## Archive and Privacy

- [ ] `composer run audit:cleanup` passed, then `scripts/package-source.ps1` was run with the submitted commit, reviewed UML directory, reviewed 8-10 screenshot directory, and a new output path.
- [ ] `GuideMyPC-Source.zip` contains only the required `frontend/`, runnable `backend/`, `database/`, `uml/`, `docs/`, README, and verified SHA-256 manifest layout; it was inspected for `.env`, secrets, local data, uploads, logs, backups, dependencies, Git metadata, and workspace tooling.
- [ ] `scripts/verify-source-package.ps1` was given the exact 40-character release SHA, verified the archive root and complete unique manifest binding, configured a clean extraction from `.env.example`, installed the lockfile, migrated and seeded distinct disposable runtime and `_test` databases, ran the full suite, passed the runtime public-root matrix, and removed both databases.
- [ ] Final document properties, image metadata, screenshots, and evidence contain no personal data, credentials, local paths, tokens, or private content.
- [ ] The outer submission ZIP contains all four required artifacts with instructor-approved names and formats.
- [ ] The independent reviewer opened the outer ZIP and signed the final result.

## Phase 5 Artifact Results

- [x] Created the separate `Readme.docx` with required private team details.
- [x] Created and proofread the Hebrew `GuideMyPC-Final-Report.docx`.
- [x] Created `GuideMyPC.vpp` containing all four required UML diagrams and
  inspected readable exports.
- [x] Captured, redacted, captioned, and inspected exactly ten screenshots
  against the release-candidate worktree; exact commit binding is deferred to
  the package manifest.

## Remaining Phase 6 Validation and Packaging

- [x] Ran the release-candidate full automated suite against an isolated fresh database.
- [x] Completed isolated live Apache route and private-path checks.
- [x] Completed keyboard, Chrome accessibility-tree/semantic, browser,
  responsive, and safe error-state validation across representative routes.
- [x] Rehearsed backup and restore and retained non-sensitive evidence.
- [ ] Generate the strict final source package and pass prohibited-content and
  secret/PII scans.
- [ ] Install and validate a clean extraction.
- [ ] Assemble, open, and independently review the final outer package.

## Final Decision

| Decision | Assembler signature/date | Independent reviewer signature/date |
| --- | --- | --- |
| Ready to submit / blocked with reason: `[record decision]` |  |  |
