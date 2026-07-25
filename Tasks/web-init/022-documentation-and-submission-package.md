# Task: Documentation and Academic Submission Package

- Status: In progress
- Priority: Critical
- Release: Submission
- Dependencies: `000-product-scope-and-architecture.md` for drafting; `020-testing-security-and-release.md` for finalization; `021-production-deployment.md` only when production deployment is claimed

> **Final-release scope notice (2026-07-22):** The report, diagrams, and screenshots must represent only verified-core scope. Omit AI Assistant, Uploads, Maintenance Center, Knowledge administration, product Reports, full-resource APIs, Donate, Community v2, unproven mail/CSV options, clean URLs/framework/SPA/ORM/template-engine changes, and production-hosting claims. Describe the platform as validated local XAMPP/Apache/PHP/MariaDB unless separate evidence proves more. Apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Produce a truthful, professionally organized academic submission that documents the implemented GuideMyPC release, demonstrates team contribution and engineering decisions, and packages the required evidence without leaking secrets or generated local data.

## Current State

Version-controlled submission sources now include a system overview, report outline, team-record template, test-evidence record, screenshot manifest, third-party inventory, and a tested source-archive script. Final personal team details, screenshot captures, Word files, the Visual Paradigm project, and the outer submission ZIP remain intentionally uncreated until the final tested release and instructor requirements are confirmed. The root `README.md` is intended for project setup and must not be confused with the separately submitted team `Readme.docx`.

## Reference Inputs

- `screenshot giudes/` records the academic report, diagram, screenshot, evidence, and packaging rubric.
- `TruckSys Pro v2.0 — מדריך פיתוח מלא.pdf` is an implementation example for PHP/XAMPP structure and presentation, not GuideMyPC source code or a template to copy.
- Reference assets must not be included in the repository or submission archive unless redistribution is authorized and the instructor explicitly requires them.

## Required Deliverables

Prepare the four rubric deliverables as separate files before placing them in the final submission archive:

1. `Readme.docx`: project name, group name, every member's name/ID/email/phone as required, role, owned deliverables, and review responsibilities. This is separate from the final report.
2. `GuideMyPC-Final-Report.docx`: the complete report, including the two-page system overview, architecture analysis, screenshots, Git/GitHub evidence, third-party inventory, tests, limitations, and references.
3. `GuideMyPC.vpp`: the Visual Paradigm source project containing the four required UML diagrams, plus PNG or PDF exports embedded in the report.
4. `GuideMyPC-Source.zip`: a clean, reproducible source archive from the exact release commit.

Use Markdown source documents under `docs/submission/` where useful for review and version history, but do not substitute Markdown, PlantUML, Mermaid, or PDF for Word or `.vpp` deliverables unless the instructor explicitly approves an alternative.

## Scope

- Record team roles, task ownership, reviewers, and contribution boundaries early and update them when work changes.
- Write a concise system overview limited to two pages: abstract, problem, target audience, main capabilities, unique value, and implemented scope.
- Consolidate frontend, backend, database, platform, folder, request, and data-flow analysis with justified choices and alternatives.
- Create four UML diagrams that reflect the implemented release.
- Capture 8-10 high-quality screenshots in logical user-flow order, including error and responsive states.
- Document real Git/GitHub workflow and evidence without inventing branches, issues, pull requests, reviews, or contribution statistics.
- Inventory every third-party runtime, development, browser, CDN, font, icon, testing, export, and diagram dependency.
- Include test evidence, known limitations, security/privacy constraints, and optional appendices where they add value.
- Build and verify a clean source archive and final submission checklist.
- Meet the formal deadline of 31/07/2026 at 23:59; use 30/07/2026 as the internal submission deadline.

## Formal Report Structure

Build `GuideMyPC-Final-Report.docx` with a table of contents, page numbers, Hebrew proofreading, and these numbered sections:

1. Truthful team roles, responsibilities, and contribution percentages aligned with Git history.
2. A prose-only system description of no more than two pages.
3. Internet technologies: architecture block diagram; frontend; backend; database; and platform.
4. Eight to ten numbered, captioned screenshots in logical user-flow order.
5. Use Case, Class, Activity, and State Machine UML diagrams.
6. Optional, truthful GitHub evidence.
7. Complete third-party inventory with exact versions.
8. Only useful appendices, such as test evidence, ER/deployment diagrams, sources, or known-problem notes.

## Non-Goals

- Adding product features solely to make the report appear larger
- Claiming architecture, testing, deployment, reviews, or team work that did not occur
- Replacing the selected PHP/MariaDB architecture with the TruckSys example stack
- Copying TruckSys code, screenshots, diagrams, text, credentials, or branding
- Using future-roadmap features in diagrams as if they are implemented
- Including secrets, private user data, personal troubleshooting content, or unredacted production evidence

## Two-Page System Overview

The system-overview section must contain:

1. Abstract: a short description of what GuideMyPC is and what the submitted release does.
2. Problem: fragmented, inconsistent, and sometimes unsafe consumer technology support.
3. Target audience: everyday users from beginner through advanced, with emphasis on people who need clear guidance.
4. Main capabilities: search, reviewed guides, diagnostics, trusted downloads, accounts, AI assistance, maintenance, and community features actually included in the release.
5. Unique value: one connected, safety-conscious path from symptom to explainable next action and verified resources.

Anything not implemented must be labeled future work rather than described as current behavior.

## Architecture Evidence

Document the implemented architecture from task `000`:

- Frontend: server-rendered HTML, custom responsive CSS, and vanilla JavaScript progressive enhancement.
- Backend: PHP 8.2 routes with shared bootstrap, security, validation, service/helper, HTML, and bounded JSON response conventions.
- Database: MariaDB 10.4 with centralized `mysqli`, prepared statements, migrations, foreign keys, constraints, and measured indexes.
- Platform: Apache/PHP/MariaDB through XAMPP for local development and hardened PHP hosting for production.
- Data flow: browser to Apache/PHP route, bootstrap/security, validation/service, prepared query, MariaDB, and HTML or bounded JSON response.

For each layer, state the selected technology, alternatives considered, why the choice fits this project, known tradeoffs, and the condition that would justify reconsideration.

The database section must include an ER diagram or table list, key fields, relationships, and relevant indexes. The platform section must name supported browsers and confirm responsive behavior down to a 320px viewport.

## UML Deliverables

Create these diagrams in the required Visual Paradigm project:

1. Use Case Diagram: guest, registered user, editor/moderator when implemented, and administrator interacting with public help, account, diagnostic, community, and administration capabilities.
2. Domain Class Diagram: implemented relationships among users, categories, guides, guide steps, progress, knowledge articles, diagnostics, downloads, uploads, and community records. Do not invent object-oriented PHP classes solely to satisfy the diagram.
3. Activity Diagram: start-to-result diagnostic flow, including answer validation, branching, backtracking, recommendation, uncertainty, and escalation.
4. State Machine Diagram: content lifecycle such as draft, review, published, rejected, archived, and restored, using only states/transitions supported by the release.

Every diagram must have a readable export, consistent names, correct cardinalities/transitions, and a caption explaining what it proves.

## Screenshot Manifest

Capture exactly 8-10 report screenshots. The recommended 10-image set is:

1. Homepage desktop
2. Registration desktop
3. Login desktop
4. Profile/history desktop
5. Universal search and filters desktop
6. Repair guide or interactive diagnostic desktop
7. Administrator content-management CRUD desktop, showing the project-equivalent admin dashboard/operations view
8. Accessible error page desktop
9. Homepage mobile
10. Core troubleshooting flow mobile

If a recommended page is not implemented, replace it with another significant implemented journey and explain the choice. Include at least 2-3 responsive/mobile views.

Maintain `docs/submission/screenshots/README.md` with the filename, route, role, viewport, release commit, logical sequence, caption, alt text, capture date, and redaction notes for every image. Use real application data that contains no secrets or personal information.

## Git and Team Evidence

- Link the repository and exact submitted commit.
- Record the actual branching approach, commit history, issues, pull requests, reviews, and CI checks used by the team.
- Include contributor evidence only when repository history supports it.
- Explain deviations honestly, including direct-to-main work or missing review evidence.
- Keep commit messages focused and never commit `.env`, passwords, tokens, uploads, logs, or database backups.

## Third-Party Inventory

For every dependency, record:

- name and exact version
- runtime, development, browser/CDN, font/icon, testing, documentation, export, or diagram category
- purpose and where it is used
- canonical source URL
- license and any attribution requirement
- delivery method such as Composer, bundled asset, CDN, or local application

Reconcile the inventory against `composer.lock`, bundled assets, script/style URLs, fonts/icons, test configuration, and Visual Paradigm/export tooling. Tools used only for documentation still belong in the inventory.

## Test Evidence and Appendices

- Reference the task `020` test matrix and record the commit, environment, tester, date, steps, expected result, actual result, status, and evidence link.
- Include representative security, accessibility, responsive, migration, authorization, error, and core-flow results.
- Document known limitations and residual noncritical risks.
- Optional appendices may contain aggregate statistics, additional test output, ER/deployment/sequence diagrams, code examples, benchmarks, or troubleshooting notes.
- Clearly label appendices so optional material does not obscure required sections.

## Source Archive

Create the source archive from the exact release commit with a reviewed PowerShell packaging script or an equivalent reproducible process. Exclude:

- `.git` and repository metadata
- `.env`, credentials, tokens, private keys, and secret-bearing local configuration
- user uploads, conversations, logs, backups, database data, and production exports
- `vendor`, `node_modules`, caches, test results, coverage output, and generated build artifacts
- IDE files, operating-system metadata, temporary files, and local debug utilities
- duplicate generated report/UML files that are already separate submission deliverables
- course/reference screenshots, PDFs, and third-party examples that are not authorized submission source

Include `.env.example`, migrations, sanitized optional seeds, source code, declared dependency manifests/locks, setup instructions, verification commands, and required public assets.

## Implementation Steps

1. Confirm exact instructor naming, Word, Visual Paradigm, and archive-format requirements before generating final binaries.
2. Create version-controlled source documents and a team ownership table.
3. Draft the two-page overview and architecture analysis from task `000`.
4. Build the four UML diagrams from the implemented schema, routes, permissions, and workflows.
5. Maintain the dependency inventory as tools/libraries are added.
6. Capture screenshots only after their corresponding routes pass task `020` checks.
7. Record Git/GitHub and manual test evidence against the release commit.
8. Export the Word and UML deliverables and inspect every page for clipping, unreadable text, broken links, or missing captions.
9. Build the source archive, scan it for secrets/private data, and test a fresh extraction.
10. Complete the final checklist and package the four required deliverables together using the required naming convention.

## Database Changes

None. Diagrams and schema descriptions must be generated from the migration-managed release schema and must not require report-only tables or changes.

## Security and Privacy

- Redact emails, IP addresses, tokens, file paths, uploaded screenshots/logs, AI conversations, and personal account data from evidence.
- Use sanitized demonstration accounts and content.
- Never place active credentials in Word files, diagrams, screenshots, setup examples, or archives.
- Run a secret scan on both the source archive and final submission package.
- Verify image metadata and document properties do not reveal unnecessary personal or machine information.

## Accessibility

- Add captions and meaningful alt text for screenshots and diagrams in the Word report.
- Use real headings, readable contrast, descriptive links, table headers, logical reading order, and language metadata.
- Do not use color as the only meaning in diagrams or test results.
- Verify exported documents remain readable at common zoom levels and that diagram text is legible when printed.

## Affected Files

- `Tasks/web-init/README.md`
- `docs/team/README.md`
- `CONTRIBUTING.md`
- `docs/submission/` source documents, screenshot manifest, evidence, inventories, appendices, and checklist
- Visual Paradigm `.vpp` source and diagram exports
- generated `Readme.docx` and `GuideMyPC-Final-Report.docx`
- source packaging/preflight script
- final source and submission archives

## Acceptance Criteria

- [ ] A separate team README identifies every required member detail, role, owned deliverables, and review responsibility.
- [ ] The system overview is no more than two concise pages and covers abstract, problem, audience, capabilities, unique value, and implemented scope.
- [ ] The report justifies frontend, backend, database, and platform choices without proposing an unapproved framework rewrite.
- [ ] The report maps Sections 1-8, includes a table of contents and page numbering, and has been proofread in Hebrew.
- [ ] The database report section includes an ER/table view, key fields, relationships, and relevant indexes; the platform section names browsers and verifies 320px minimum viewport support.
- [ ] The `.vpp` project and readable exports contain all four required diagrams and match the implemented release.
- [ ] The report contains 8-10 release-quality screenshots, including at least 2-3 responsive/mobile views, with captions and no sensitive data.
- [ ] Git/GitHub evidence references real repository activity and the exact submitted commit.
- [ ] Every third-party dependency has an exact version, purpose, source, license, and delivery method.
- [ ] Test evidence and limitations are truthful, traceable, and linked to task `020` results.
- [ ] The final checklist records the release commit, artifact names, responsible reviewer, completion status, and sign-off date.
- [ ] The source archive contains no secrets, private data, generated local state, repository history, dependencies, or debug utilities.
- [ ] A fresh archive extraction can be configured from `.env.example`, install declared dependencies, migrate a blank database, and run the documented verification command.
- [ ] The final package contains the four required deliverables with instructor-approved filenames and formats.
- [ ] The outer submission ZIP is opened and checked for all four deliverables, exclusions, and a sanitized schema/sample-data package before the internal deadline.

## Validation

- Review the Word documents against the rubric line by line and inspect exported pagination.
- Open the `.vpp` project in the required Visual Paradigm version and compare every diagram with the implementation.
- Verify the screenshot manifest count, responsive coverage, captions, sequence, commit, and redactions.
- Compare Git/GitHub claims with repository history and remote evidence.
- Compare the dependency inventory with manifests, locks, assets, CDN references, and documentation tools.
- List the final archive contents, run secret/private-data checks, and verify all exclusions.
- Extract `GuideMyPC-Source.zip` into a clean temporary directory and complete setup, migration, and fast verification.
- Have a team member who did not assemble the package perform the final checklist review.

## Definition of Done

The exact tested release is represented by a complete, truthful, readable academic report and UML project, supported by traceable evidence and a clean reproducible source archive that passes the final submission checklist.
