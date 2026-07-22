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

| Artifact | Required location or filename | Responsible reviewer | Status | Checked date |
| --- | --- | --- | --- | --- |
| Team Readme | `docs/submission/documents/Readme.docx` | `[name]` | Not started |  |
| Final report | `docs/submission/documents/GuideMyPC-Final-Report.docx` | `[name]` | Not started |  |
| UML project | `uml/source/GuideMyPC.vpp` | `[name]` | Not started |  |
| Four UML exports | `uml/exports/` with the documented filenames | `[name]` | Not started |  |
| Final screenshots | 8 to 10 images in `docs/submission/screenshots/` | `[name]` | Not started |  |
| Submission archive | `build/GuideMyPC_Submission.zip` | `[name]` | Not started |  |

## Content and Evidence

- [ ] Report Sections 1-8 are present, numbered, proofread in Hebrew, and use a table of contents and page numbers.
- [ ] Team information and contribution claims match the team record and available Git evidence.
- [ ] The system description is no more than two pages and describes only the submitted release.
- [ ] Technology, database, platform, browser, and 320px claims have traceable evidence.
- [ ] Each UML diagram is present in `GuideMyPC.vpp`, legible in export, and matches implemented behavior.
- [ ] The report contains exactly 8 to 10 captioned screenshots, including at least two mobile views, with manifest entries and redactions.
- [ ] Third-party inventory names exact versions, sources, licenses, purposes, and delivery methods.
- [ ] Test evidence records the submitted commit, environment, tester, date, expected and actual result, and safe evidence reference.
- [ ] Known limitations distinguish incomplete roadmap foundations from implemented behavior.

## Archive and Privacy

- [ ] `composer run package:submission:strict` completed successfully for the submitted commit.
- [ ] `GuideMyPC_Submission.zip` contains `frontend/`, `backend/`, `database/`, `uml/`, `docs/`, `README.md`, and `PACKAGE-MANIFEST.txt` under one `GuideMyPC/` folder.
- [ ] The ZIP was opened and inspected for `.env`, secrets, local data, uploads, logs, backups, dependency folders, Git metadata, and workspace tooling.
- [ ] A clean extraction was configured from `.env.example`, migrated, optionally seeded, and passed the documented verification command.
- [ ] Final document properties, image metadata, screenshots, and evidence contain no unintended personal data, credentials, local paths, tokens, or private content.
- [ ] The independent reviewer opened the final ZIP and checked every Word, UML, image, database, frontend, and backend file.

## Final Decision

| Decision | Assembler signature/date | Independent reviewer signature/date |
| --- | --- | --- |
| Ready to submit or blocked with reason: `[record decision]` |  |  |
