# Final Project Submission Readiness

## Purpose And Authority

This is the durable execution record for final-submission readiness. For
conflicting planning material, use this order of authority:

1. The course final-project requirements and required deliverable formats.
2. Demonstrated release behavior and repeatable validation evidence.
3. `docs/route-contracts.md`, `docs/route-inventory.md`, and
   `docs/project-structure.md`.
4. This readiness record and its linked evidence.
5. Historical task plans, which do not expand final-release scope.

The canonical runtime remains the flat compatibility project. Do not move root
compatibility routes or reorganize the canonical repository into submission
folders. The submission package is a categorized review artifact; its
`backend/` copy must be independently runnable with `backend/public/` as the
only web root.

## Verified-Core Scope

The submitted product includes published Guides, approved Downloads, Search,
accounts and progress, public read-only Knowledge, Diagnostics, role-aware
Dashboard KPI/chart views, canonical legacy Community posts/comments/likes,
documented administration workflows, and the two narrow Search JSON endpoints.

The final product excludes AI Assistant, Uploads, Maintenance Center, Knowledge
administration, product Reports, full-resource APIs, Donate, Community v2,
unproven mail/CSV behavior, clean URLs, framework/SPA/ORM/template-engine
migration, and production-hosting claims. Legacy `*.php` routes remain
canonical. Retired `ai.php` and `donate.php` requests use the standard safe
404 response.

## Required Change Discipline

Before removing, moving, merging, or rewriting tracked source, document its
callers, dependencies, route contract, tests, package role, and rollback path.
Historical database migrations remain immutable. Preserve route, form,
redirect, session, JSON, sitemap, robots, and public-root contracts unless an
owner-approved retirement is documented in `docs/route-contracts.md`.

Use the complete inventory and reorganization evidence before changing package
or release structure:

- `docs/submission/file-inventory.md`
- `docs/submission/reorganization-plan.md`
- `docs/submission/cleanup-evidence.md`
- `docs/submission/hardening-evidence.md`
- `docs/submission/release-hardening-evidence.md`
- `docs/submission/artifact-evidence.md`

## Release Gates

Do not claim technical readiness for a changed release until all applicable
gates pass against its exact commit:

- Composer validation, current PHP lint, helper/route/authorization checks,
  and `composer run audit:cleanup`.
- Fresh and repeat migrations, seed, and the complete isolated `_test` suite.
- Isolated Apache with only `public/` exposed, including retired/private-path
  checks and Search endpoint response contracts.
- Browser checks for responsive/accessibility behavior and the rendered package
  homepage.
- Strict source-package build and clean extraction. The extracted `backend/`
  must independently provide canonical CSS/JS assets, compatibility rewrites,
  non-duplicated application URLs, migrations/seeds, and the full test suite;
  `frontend/` is never a runtime dependency.
- A source manifest bound to the exact commit, approved UML source/exports, and
  8-10 reviewed screenshots. Generated ZIPs, DOCX files, VPP files,
  screenshots, secrets, runtime data, dependencies, and personal information
  remain outside Git.

## Current Status

Release commit `7866bd238a47f3863d729e9d69c566f494201704` has historical
clean-extraction evidence: Apache rooted at extracted `backend/public`,
canonical and compatibility asset checks, Chrome-rendered stylesheet/navigation
checks, fresh runtime/test databases, migrations, seeds, and the complete test
suite all passed.

This restored governance document is a later source change. Before this branch
is treated as technically ready, create its exact release commit and rerun the
applicable package and clean-extraction gates against that commit. Independent
human/instructor review and acceptance remain required after the technical
gates pass.
