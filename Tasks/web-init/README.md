# GuideMyPC Web Initialization Roadmap

This directory converts the GuideMyPC product requirements into executable development tasks for the existing PHP and MariaDB application.

## Target Stack

- Local runtime: XAMPP with Apache, PHP 8.2, and MariaDB 10.4
- Application: server-rendered PHP with progressive enhancement in vanilla JavaScript
- Database access: `mysqli`, centralized behind shared application functions
- Styling: responsive custom CSS with accessible reusable components
- Dependency management: Composer where a maintained package provides clear value
- Production: hardened PHP hosting with HTTPS; XAMPP is for local development only

The current prototype will be improved incrementally. A React or Node.js rewrite is outside the MVP scope.

## Release Slices

| Release | Goal | Tasks |
| --- | --- | --- |
| R0 | Secure, repeatable foundation | `000`-`004` |
| R1 | Find and follow trusted solutions | `005`-`009` |
| R2 | Personalized troubleshooting | `010`-`013` |
| R3 | Trusted AI and download assistance | `014`-`016` |
| R4 | Community, operations, and launch | `017`-`021` |
| Submission | Academic documentation and verified evidence | `022` |
| Future | Post-MVP product expansion | `100` |

## Execution Order

1. Complete tasks in numeric order unless a task explicitly permits parallel work.
2. Do not begin public AI or file-upload features before the security foundation is complete.
3. Treat each release boundary as a usable, testable product increment.
4. Run the validation listed in every task before marking it complete.
5. Update task status and record material decisions in the task file during implementation.
6. Maintain team ownership, architecture decisions, dependency inventory, Git/GitHub evidence, test evidence, and screenshot notes during development rather than reconstructing them at submission time.
7. Finalize task `022` against the exact commit that passes task `020`.

## Task Index

- [`000-product-scope-and-architecture.md`](000-product-scope-and-architecture.md): product scope, releases, architecture, and quality constraints
- [`001-xampp-local-setup.md`](001-xampp-local-setup.md): repeatable local installation and environment setup
- [`002-security-bootstrap.md`](002-security-bootstrap.md): sessions, CSRF, authorization, input handling, and private files
- [`003-database-migrations-and-seeds.md`](003-database-migrations-and-seeds.md): versioned schema and sanitized seed data
- [`004-application-structure-and-error-handling.md`](004-application-structure-and-error-handling.md): shared bootstrap, helpers, logging, and errors
- [`005-responsive-design-system-and-layout.md`](005-responsive-design-system-and-layout.md): accessible visual system and responsive shell
- [`006-homepage-and-navigation.md`](006-homepage-and-navigation.md): PRD homepage and navigation
- [`007-universal-search.md`](007-universal-search.md): global search, suggestions, filters, and related searches
- [`008-knowledge-base.md`](008-knowledge-base.md): articles, error codes, FAQs, and glossary
- [`009-structured-repair-guides.md`](009-structured-repair-guides.md): safe step-by-step guides, videos, progress, and print view
- [`010-authentication-and-user-profile.md`](010-authentication-and-user-profile.md): secure accounts and personalized history
- [`011-diagnostic-engine.md`](011-diagnostic-engine.md): branching diagnostic flows and saved sessions
- [`012-repair-confidence-meter.md`](012-repair-confidence-meter.md): explainable likely-cause ranking
- [`013-maintenance-center.md`](013-maintenance-center.md): scheduled device-care recommendations
- [`014-trusted-downloads.md`](014-trusted-downloads.md): verified official downloads and review workflow
- [`015-ai-assistant.md`](015-ai-assistant.md): safe, contextual AI troubleshooting
- [`016-secure-file-uploads.md`](016-secure-file-uploads.md): screenshot and text-log uploads
- [`017-community-forum.md`](017-community-forum.md): questions, answers, voting, and accepted solutions
- [`018-admin-and-content-moderation.md`](018-admin-and-content-moderation.md): unified content and moderation operations
- [`019-accessibility-seo-and-performance.md`](019-accessibility-seo-and-performance.md): WCAG, discoverability, and speed
- [`020-testing-security-and-release.md`](020-testing-security-and-release.md): automated checks and MVP release gate
- [`021-production-deployment.md`](021-production-deployment.md): secure production operations and rollback
- [`022-documentation-and-submission-package.md`](022-documentation-and-submission-package.md): academic report, diagrams, screenshots, evidence, and clean submission archive
- [`100-future-roadmap.md`](100-future-roadmap.md): deferred Phase 2 and Phase 3 capabilities

## Standard Status Values

- `Not started`
- `In progress`
- `Blocked`
- `In review`
- `Complete`

## Definition of MVP

The runtime MVP is complete when R0 through R4 pass the release gate in task `020`. Task `022` is a separate academic submission gate and does not add runtime features or authorize an architecture rewrite. Future-roadmap work is not required for MVP launch or submission.
