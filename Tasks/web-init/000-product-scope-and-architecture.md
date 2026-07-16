# Task: Product Scope and Architecture

- Status: Not started
- Priority: Critical
- Release: R0
- Dependencies: None

## Objective

Establish a shared implementation boundary for GuideMyPC v1.0 so development can extend the existing prototype without an unnecessary rewrite or an unbounded first release.

## Product Direction

**Product:** GuideMyPC
**Tagline:** Your Trusted Tech Support Companion
**Vision:** Become a trusted self-help destination where everyday users can search, diagnose, understand, and safely resolve consumer technology problems.

Primary users include students, parents, seniors, home-office users, small business owners, casual gamers, and users at beginner through advanced skill levels.

Initial support domains are Windows, macOS, Linux, Android, iPhone/iPad, Wi-Fi, and routers. Printers, NAS devices, smart-home products, and consoles remain future expansions.

## Current State

- The repository is a functional procedural PHP prototype running from `C:\xampp\htdocs\GuideMyPC`.
- Shared page fragments exist under `includes/`; CSS and JavaScript are under `css/` and `js/`.
- MariaDB schema and sample data are stored in `database/guidemypc.sql`.
- Accounts, guides, progress, ratings, favorites, downloads, community features, and admin pages are partially implemented.
- Universal search is nonfunctional, `ai.php` is empty, and the generic Guides navigation has a broken empty-category flow.
- Security, migrations, tests, setup documentation, monitoring, and deployment processes are not release-ready.

## Architecture Decisions

1. Keep PHP 8.2, Apache, and MariaDB for the MVP.
2. Use XAMPP only for local development, not public production hosting.
3. Improve the current application incrementally rather than introduce a parallel framework rewrite.
4. Centralize bootstrap, configuration, authorization, validation, and database operations before adding complex features.
5. Keep server-rendered pages usable without JavaScript; use JavaScript for enhancement and interactive diagnostics.
6. Use MariaDB full-text search for the first release. Evaluate Meilisearch only if measured search quality or scale requires it.
7. Keep AI provider calls behind a server-side adapter so keys and provider-specific behavior never reach the browser.
8. Store uploads outside the public web path where possible and serve them through authorized PHP endpoints.
9. Introduce Composer only for maintained dependencies that reduce security or operational risk.

## Release Scope

### R0: Foundation

- Repeatable XAMPP setup
- Environment configuration and protected private files
- Secure sessions, authentication helpers, CSRF, validation, and error handling
- Versioned migrations and sanitized seeds
- Test foundation

### R1: Find and Follow Solutions

- Responsive homepage and navigation
- Universal search and common-problem browsing
- Knowledge base, glossary, FAQs, and error codes
- Structured repair guides with warnings, tools, checklists, videos, and print view

### R2: Personalized Troubleshooting

- Hardened accounts, favorites, history, and saved progress
- Branching diagnostic flows
- Explainable Repair Confidence Meter
- Maintenance Center

### R3: Trusted Assistance

- Verified download catalog
- AI troubleshooting assistant with safety rules and session context
- Moderated screenshot and text-log uploads

### R4: Community and Launch

- Questions, answers, voting, accepted solutions, reporting, and moderation
- Unified administration and audit history
- Accessibility, SEO, performance, automated testing, and deployment readiness

## Non-Goals

- React, Next.js, Node.js, or PostgreSQL migration during MVP
- Native mobile applications
- Remote desktop access or live expert support
- Automatic hardware scanning
- Voice chat
- Reputation economy beyond basic helpful-vote data
- Paid support tiers or content paywalls
- Advertising networks

## Product Safety Rules

- Recommend reversible, low-risk actions before invasive repair steps.
- Warn before data loss, firmware changes, registry edits, account resets, or hardware disassembly.
- Recommend backups before major operating-system, disk, or firmware changes.
- Prefer official manufacturer documentation and download sources.
- Never imply that diagnostic confidence is certainty.
- Do not expose secrets, private uploads, logs, or personally identifying information to unauthorized users.
- AI output must not bypass content verification or upload controls.

## Success Metrics

- Search success rate and zero-result rate
- Guide completion and reported-resolution rate
- Diagnostic completion and likely-cause usefulness
- AI resolution and escalation rates
- Average time to useful next step
- User satisfaction and returning visitors
- Account sign-ups and saved-item usage
- Community response and accepted-solution rates
- Download verification freshness
- Donation conversion after MVP, without intrusive prompts

Metrics must be privacy-conscious and must not store raw sensitive troubleshooting content unless required and disclosed.

## Acceptance Criteria

- [ ] Stakeholders agree that PHP/XAMPP is the MVP development target.
- [ ] R0-R4 scope and future non-goals are accepted.
- [ ] Every PRD capability is assigned to an MVP task or the future roadmap.
- [ ] Safety principles are referenced by diagnostics, guides, downloads, uploads, and AI tasks.
- [ ] Production use of XAMPP is explicitly prohibited.
- [ ] Any architecture changes discovered during implementation are recorded here or in a linked decision note.

## Validation

- Review all files listed in `Tasks/web-init/README.md` for scope coverage and dependency consistency.
- Confirm no MVP task assumes React, Node.js, PostgreSQL, Redis, or cloud-only infrastructure.

## Definition of Done

This task is complete when the architecture and release boundaries are approved and downstream tasks can be estimated without unresolved platform decisions.
