# Submission Artifact Evidence

Artifact update date: 2026-07-29
Working branch: `report-compliance-fix`
Release identity: the exact final local commit is recorded in the ignored DOCX
files and generated archive manifest after the authorized release commit.

This record covers ignored private/generated submission artifacts. Word files,
personal information, UML files, screenshot PNGs, and ZIP files are not added

## Visual Paradigm And UML

- `docs/submission/uml/source/GuideMyPC.vpp` is a native editable Visual
  Paradigm project with Use Case, Class, Activity, and State Machine diagrams.
- The four PNG exports are `use-case.png`, `class-diagram.png`,
  `activity-diagram.png`, and `state-machine.png`.
- The Class Diagram remains a native-VPP-controlled artifact. Before final
  packaging, its VPP source and PNG export must be checked together for exact
  terminology and camelCase consistency; the PNG is never treated as an
  independent source of truth.
- The diagrams cover only retained Guest, Member, Editor, and Administrator
  behavior. Excluded product areas are not presented as active capabilities.

## Architecture And ER Figures

- `docs/submission/diagrams/architecture.svg` is the retained source for the
  1800x1100 architecture PNG. It depicts browser-to-Apache-to-PHP-to-MariaDB
  request handling and the two bounded Search JSON endpoints.
- `docs/submission/diagrams/er-diagram.svg` is the retained source for the
  1900x1200 concise ER PNG. It depicts only migration-backed central entities,
  real field names, and real foreign-key relationships.

## Screenshots

- Exactly ten PNG screenshots were captured from the local reviewed tree using
  seeded public content and sanitized test states. No real account data is used.
- The set covers Home, public Downloads, Downloads administration, desktop
  Login, mobile Login, user Dashboard, Guide administration, Diagnostics, a
  safe 404 response, and mobile Downloads.
- Eight captures are desktop layouts and two are genuine 320x800 mobile
  layouts. The mobile Login capture replaced the less relevant desktop
  remembered-devices capture, preserving a total of ten images.
- Captures contain no password, cookie, token, session ID, email address,
  private IP address, local path, or personal data. Per-file route, role,
  caption, alt text, viewport, date, and redaction notes are in
  `screenshots/README.md`.

## Word Documents

- `Readme.docx` remains the private source of student identity and contact
  details. It is not copied into Markdown files.
- `GuideMyPC-Final-Report.docx` is the Hebrew verified-core report with a
  cover, contents, page numbers, architecture and ER figures, four UML figures,
  and ten captioned screenshots.
- After the final Word edit, the document must be reopened, fields and the table
  of contents must be updated, and external relationships, comments, tracked
  changes, layout, page count, and Hebrew RTL rendering must be checked.
- No final approval, independent review, or human signature is claimed here.

## Remaining Gate

Correct the native Class Diagram and export it from VPP, run current repository
validation, create the authorized release commit, record that exact SHA in
ignored generated artifacts, then rebuild and inspect both archives. Obtain any
required human review separately.
