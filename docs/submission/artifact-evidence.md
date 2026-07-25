# Phase 5 Submission Artifact Evidence

Date: 2026-07-23
Branch: `final-project-submission-readiness`
Release identity: final commit pending the required pre-commit gate

This record covers the private/generated Phase 5 artifacts. Their directories
are intentionally ignored; none of the Word documents, personal details, UML
files, screenshots, or ZIP files may enter Git. The final package builder
overlays only the reviewed UML and screenshot artifacts after the release
commit exists.

## Visual Paradigm And UML

- Visual Paradigm Community Edition 18.1 was installed from the official
  `VisualParadigm.VisualParadigm.Community` winget package and used under its
  non-commercial terms.
- `docs/submission/uml/source/GuideMyPC.vpp` is a native, editable Visual
  Paradigm project. It opens in Visual Paradigm and contains four native
  diagrams: Use Case, Class, Activity, and State Machine.
- Four scope-equivalent 1600x1000 PNG exports were inspected for legibility:
  `use-case.png`, `class-diagram.png`, `activity-diagram.png`, and
  `state-machine.png`.
- The diagrams depict only Guest, Member, Editor, Administrator, retained
  feature boundaries, transactional Diagnostic transitions, and the active /
  completed / expired Diagnostic lifecycle. Excluded product features are not
  presented as implemented behavior.
- Visual Paradigm's generated local-author metadata was replaced byte-for-byte
  with the neutral value `author`; the sanitized project reopened successfully
  with all four diagrams.
- The sanitized VPP size is 917,504 bytes and its pre-package SHA-256 is
  `3F8BB3EE0DDA256DB217FF48A8465998BA2B5500D5FE445DEEBE31B42F0756D0`.
  Final package hashes remain authoritative after commit binding.

## Screenshots

- Exactly ten PNG screenshots were captured from the reviewed working tree
  against disposable `guidemypc_screenshot_test` data. No real account or
  production data was used.
- The authenticated screenshots use the sanitized identity `Submission
  Reviewer`; cookies were cleared before the unknown-route screenshot.
- The set covers Home, Guides, mobile Guide, public Knowledge, Search,
  Diagnostics, canonical Community, administrator Dashboard, Guide
  administration, and a mobile safe 404. Viewports are eight 1440x900 desktop
  captures and two 320x800 mobile captures.
- Visual review found no session IDs, request IDs, credentials, local paths,
  real PII, or claims for AI Assistant, Donate, Uploads, Maintenance Center,
  Knowledge administration, product Reports, full-resource APIs, or Community
  v2.
- Total image size is 787,826 bytes. The sorted
  `filename:SHA-256` aggregate is
  `270A24A4B67247FEE92170AFB569950E0FA9D16AC971925FAB31C8DFBAB6C120`.
  Individual names, roles, routes, captions, dates, viewports, and redaction
  notes are recorded in `screenshots/README.md`.

## Private Word Documents

- Microsoft Word 16.0 build 16.0.20131 generated and reopened both documents.
- `docs/submission/documents/Readme.docx` is six pages with two structured
  tables, the owner-supplied private identity/contact fields, a 100%
  contribution statement, concrete responsibilities, release identity field,
  and privacy notice.
- `docs/submission/documents/GuideMyPC-Final-Report.docx` is a proofread Hebrew
  verified-core report with a cover, contents, numbered sections, limitations,
  page numbers, four UML figures, and exactly ten captioned screenshots.
- Word rendered the report to a 28-page review PDF. The review confirmed
  correct Hebrew metadata, readable text, one complete figure per appendix
  page, full-width desktop captures, bounded mobile captures, and no external
  image dependency.
- Open XML inspection found 14 embedded media files, 14 drawing nodes, and zero
  external relationships in the final report. The Readme has zero external
  relationships.
- Hidden DOCX `Author` and `Last Author` properties were normalized to
  `GuideMyPC Release`; both documents reopened afterward with the same six- and
  28-page layouts.
- Both documents contain `FINAL_COMMIT_PENDING`. After the one authorized
  release commit, this placeholder must be replaced in the ignored documents
  with the exact SHA before final package assembly.

## Privacy And Ignore Evidence

- `.gitignore` excludes `/docs/submission/documents/`,
  `/docs/submission/uml/`, generated screenshot image extensions, VPP/DOCX
  root filenames, and source/submission ZIP names.
- `git check-ignore -v` confirmed both DOCX files, the VPP, UML exports, and
  screenshots are ignored.
- A tracked-source scan found none of the supplied student ID, email, phone, or
  full name. Tracked Markdown retains placeholders only.

## Remaining Gate

These artifacts are complete but are not yet final-release sign-off. Required
next steps are: final pre-commit validation, the single authorized commit,
replacement of `FINAL_COMMIT_PENDING` in ignored Word files, exact-commit
source package generation with reviewed UML/screenshots, strict clean
extraction, outer four-artifact package assembly, and final review.
