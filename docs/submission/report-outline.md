# GuideMyPC Final Report Outline

Use this as the reviewed Markdown source for `GuideMyPC-Final-Report.docx`. The final report must be proofread in Hebrew, include a table of contents and page numbers, and remain truthful to the submitted release commit.

## Front Matter

- Project: GuideMyPC
- Submitted release commit: `[record final commit]`
- Submission date: `[record date]`
- Group: `[record group name]`
- Repository: `https://github.com/jkhwaj/GuideMyPC`

## 1. Team Roles and Contributions

Insert each member's required name, student ID, contact details where course policy requires them, assigned role, owned deliverables, reviewer, and contribution percentage. Reconcile contribution claims with the Git history and team record; do not infer work from commit counts alone.

## 2. System Description (Maximum Two Pages)

Adapt `system-overview.md` into concise prose covering:

- The consumer-technology support problem and its safety implications.
- The beginner-to-advanced audience.
- Implemented capabilities only: published guides and knowledge content, search, accounts and progress, diagnostics, trusted resources, and the implemented administration foundation.
- The connected path from symptom to explainable next action and reviewed resource.
- Explicitly labeled future work for unfinished AI-provider, community, upload, and broader moderation workflows.

## 3. Internet Technologies

### Architecture

Include a block diagram showing browser, Apache, PHP route/bootstrap/security/helper layers, MariaDB, and HTML or bounded JSON responses. Explain why incremental server-rendered PHP was selected over an API-first JavaScript rewrite: it preserves the existing application, keeps operational complexity low, and fits the project deadline. State the tradeoff that a richer separate client would require a stable API and stronger deployment/observability maturity.

### Frontend

Document server-rendered HTML, custom responsive CSS, progressive vanilla JavaScript, semantic forms, flash messages, and no third-party font or icon assets. Confirm final browser checks and responsive behavior at 320px before submission.

### Backend

Document PHP 8.2 routes, shared `config.php` bootstrap, sessions, CSRF, authorization, validation, prepared statements, transactions, redacted error logging, and bounded JSON conventions. Use the guide-admin workflow as a concrete audited mutation example.

### Database

Include an ER diagram or concise table list generated from migrations. Cover users, categories, guides, guide steps, guide tools, user progress, knowledge articles, diagnostic flows, downloads, community records, and audit events as implemented. Identify foreign-key relationships, unique slugs, and relevant search/list indexes without claiming tables or states that are only planned.

### Platform

Record the final tested versions of Windows, XAMPP, Apache, PHP, MariaDB, and browsers. Explain that local development uses XAMPP and production deployment is not claimed until the production checklist is completed.

## 4. Screenshots

Embed exactly 8-10 numbered screenshots in logical flow order. Use the completed manifest for route, role, viewport, caption, alt text, capture date, release commit, and redactions. Include at least two mobile views and one safe error state. Do not include real account data, tokens, local paths, or instructor/reference material.

## 5. UML Diagrams

Create and export the following diagrams from `GuideMyPC.vpp`:

1. Use Case: guest, registered user, and administrator interactions actually available in the release.
2. Domain Class: schema relationships, not invented PHP classes.
3. Activity: diagnostic start-to-result path, including validation, branching, backtracking, uncertainty, and escalation where implemented.
4. State Machine: only lifecycle states and transitions supported by the submitted release.

Give every diagram a readable export and a caption that explains what it demonstrates.

## 6. Git and GitHub Evidence

Record the repository URL, submitted commit, branching model, and real checks. The current history uses direct commits to `main`; do not claim pull requests, code reviews, issues, or CI runs unless evidence exists. Include only screenshots or exports that do not disclose private account information.

## 7. Third-Party Inventory

Use `third-party-inventory.md`. Reconcile the final table with runtime versions, committed assets, external service integrations, and the document/UML tooling actually used for submission.

## 8. Appendices

Include only material that supports the report: test evidence, known limitations, security/privacy notes, ER/deployment details, selected source references, and the final archive checklist. Identify every item by release commit and date.
