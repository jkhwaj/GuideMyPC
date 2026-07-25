# Submission Sources

This directory contains reviewable Markdown sources and readiness evidence. It
does not replace the instructor-required Word, Visual Paradigm, screenshot, or
package artifacts. The Word, UML, and screenshot artifacts were created and
inspected in Phase 5; exact-commit package validation and final sign-off remain.

## Verified-Core Scope

Final report, UML, and screenshot claims are limited to the verified core:

- published Guides, approved Downloads, Search, accounts, progress, and their
  documented administration workflows;
- public Knowledge reads without Knowledge administration;
- Diagnostics, the role-aware Dashboard, and its KPI/chart projections;
- the canonical legacy Community post/comment/like workflow; and
- the bounded `search_suggestions.php` and `search_event.php` JSON endpoints,
  which are not a full-resource API.

Root legacy `*.php` URLs remain the canonical release URLs. Clean URLs, a
framework, SPA, ORM, template engine, and production hosting are not release
claims.

The final product scope excludes AI Assistant, Uploads, Maintenance Center,
Knowledge administration, product Reports, full-resource APIs, Donate,
Community v2, and unproven mail or CSV behavior. Historical migrations or
dormant schema do not make those product features complete.

## Source Status

| Source | Supports | Current status |
| --- | --- | --- |
| `system-overview.md` | Maximum two-page system description | Scope-aligned source incorporated into the inspected Hebrew report |
| `report-outline.md` | Required final-report sections | Scope-aligned source incorporated into the inspected 28-page final report |
| `test-evidence.md` | Test appendix and release evidence | Phase 2-4 worktree evidence recorded; final exact-package validation pending |
| `third-party-inventory.md` | Dependency and tooling section | Exact runtime, verification, Word, and Visual Paradigm versions recorded |
| `screenshots/README.md` | Screenshot appendix | Exactly ten ignored, captioned, visually reviewed captures recorded |
| `final-checklist.md` | Final artifact and package sign-off | Open checklist; no final sign-off claimed |
| `artifact-evidence.md` | Phase 5 artifact evidence | Native VPP, four exports, ten screenshots, two DOCX files, ignore/privacy, and visual review recorded |
| `docs/team/README.md` | Separate team Readme source | Public template remains sanitized; private `Readme.docx` created and ignored |
| Submission packaging | Strict final source and outer package | Packaging, prohibited-content review, clean extraction, and independent review pending |

Before submission, bind the inspected `Readme.docx`, Hebrew
`GuideMyPC-Final-Report.docx`, `GuideMyPC.vpp`, four UML exports, and ten
screenshots to the final release commit and strict package manifest. Use the
exact release SHA, not a moving `HEAD`, for packaging and final evidence.

Do not commit personal contact information, final Word files, Visual Paradigm
projects, screenshots, or submission ZIPs unless course policy explicitly
requires them in the repository.
