# Task: Product Scope and Architecture

- Status: Foundation implemented
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

## Problem and Unique Value

People solving everyday technology problems currently move between generic search results, unverified download pages, videos, forums, and standalone AI tools. This fragmentation makes it difficult for nontechnical users to judge safety, sequence troubleshooting steps, or understand when a recommendation is uncertain.

GuideMyPC combines reviewed guides, explainable diagnostics, source-linked AI assistance, official downloads, maintenance advice, videos, and moderated community escalation in one consistent experience. Its differentiator is not the volume of content; it is the connection between safe troubleshooting steps, visible risk warnings, verified resources, and an explainable next action.

## Current State

- The repository is a functional procedural PHP prototype running from `C:\xampp\htdocs\GuideMyPC`.
- Shared page fragments exist under `includes/`; CSS and JavaScript are under `css/` and `js/`.
- Versioned migrations and sanitized seeds now cover the core content, account, diagnostic, download, maintenance, and administration foundations.
- Accounts, search, guides, knowledge content, progress, ratings, favorites, downloads, diagnostics, community records, and admin pages have varying levels of implementation.
- The shared bootstrap, CSRF/authorization helpers, local setup validation, fast integration tests, sitemap/robots, and baseline security headers are implemented.
- Later roadmap work remains foundation-level unless its complete user-facing workflow and task validation have evidence. In particular, an external AI provider and full community workflow are not release claims.

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

## Layered Architecture Analysis

### Frontend

- Selected: server-rendered HTML, custom responsive CSS, and vanilla JavaScript progressive enhancement.
- Reason: this matches the current codebase, keeps core troubleshooting usable without JavaScript, and minimizes framework and build complexity for the MVP.
- Alternatives considered: Bootstrap, Tailwind CSS, React, Vue, and Next.js.
- Reconsider when: measured interaction complexity, accessibility maintenance, or a separate client application justifies a component/build system.

### Backend

- Selected: PHP 8.2 routes with shared bootstrap, security, service, validation, and response helpers.
- Reason: PHP is already deployed through XAMPP, the prototype is functional, and an incremental structure change is less risky than a framework rewrite.
- Alternatives considered: Laravel, Node.js/Express, and a standalone REST backend.
- Reconsider when: measured maintenance or integration needs cannot be handled by the shared PHP structure.

### Database

- Selected: MariaDB 10.4 with centralized `mysqli`, native prepared statements, versioned migrations, foreign keys, constraints, and measured indexes.
- Reason: it is available in the current XAMPP environment and fits the relational content, account, diagnostic, and community data.
- Alternatives considered: PostgreSQL, MongoDB, and an ORM/PDO migration.
- Reconsider when: a concrete feature or scaling limitation cannot be solved safely with MariaDB and the current data-access boundary.

### Platform

- Selected locally: Apache, PHP, and MariaDB through XAMPP on Windows.
- Selected for production: hardened supported PHP hosting with HTTPS, least-privilege database access, private storage, monitoring, and backups.
- Reason: XAMPP gives contributors a reproducible local environment but is not a production security or operations platform.
- Alternatives considered: Docker and a JavaScript cloud stack.
- Reconsider when: team onboarding or production operations demonstrate a concrete need for containers or managed services.

## Request, Folder, and Data Flow

The implemented architecture must be documented and kept synchronized with this logical flow:

```text
Browser
  -> Apache/PHP route
  -> application bootstrap and security checks
  -> validation and feature service/helper
  -> mysqli prepared statement
  -> MariaDB
  -> server-rendered HTML or a bounded JSON response
```

The architecture documentation must also identify public routes/assets, shared application code, migrations/seeds, private uploads, logs, tests, scheduled jobs, and documentation. JSON endpoints support progressive enhancement where useful; they do not make the application API-first.

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
- AI troubleshooting shell and safety boundary only; external-provider integration requires a separate release decision
- Moderated screenshot and text-log uploads only when their security review and workflow are complete

### R4: Community and Launch

- One complete administrator content CRUD/moderation workflow with RBAC and audit history
- Community foundation work is future scope until questions, answers, reporting, and moderation work end to end
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
- [ ] The problem statement and unique value are reflected in the two-page submission overview.
- [ ] Frontend, backend, database, and platform choices identify alternatives, rationale, and reconsideration triggers.
- [ ] A current folder/request/data-flow diagram matches the implemented release rather than a speculative future system.
- [ ] Every task status distinguishes foundation work from a completed, evidenced vertical slice.

## Validation

- Review all files listed in `Tasks/web-init/README.md` for scope coverage and dependency consistency.
- Confirm no MVP task assumes React, Node.js, PostgreSQL, Redis, or cloud-only infrastructure.
- Compare the documented request and folder flow with representative public, account, admin, and JSON-enhanced routes.

## Definition of Done

This task is complete when the architecture and release boundaries are approved and downstream tasks can be estimated without unresolved platform decisions.
