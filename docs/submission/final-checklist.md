# Final Submission Checklist

Use this checklist against the exact local release commit after packaging. This
is an individual project; no independent reviewer, team member, or approval is
claimed by this document.

## Release Identity

| Field | Value |
| --- | --- |
| Repository URL | `https://github.com/jkhwaj/GuideMyPC` |
| Main branch | `main` |
| Submitted release commit | Record the exact final SHA in ignored DOCX files and the generated package manifest after the release commit |
| Project type | Individual project; one student; 100% contribution |
| Human sign-off | Required before submission |

## Deliverables

| Artifact | Required filename | Status | Human check |
| --- | --- | --- | --- |
| Student Readme | `Readme.docx` | Exists; private and ignored | Required before submission |
| Final report | `GuideMyPC-Final-Report.docx` | Exists; private and ignored | Required before submission |
| UML project | `GuideMyPC.vpp` | Exists with four native diagrams | Required before submission |
| Source archive | `GuideMyPC-Source.zip` | Existing archive must be regenerated and reverified after the release commit | Required before submission |
| Outer archive | `GuideMyPC-Submission.zip` | Existing archive must be rebuilt after source-archive verification | Required before submission |

## Content Checks

- [x] The report uses the required sections 1 through 8 and singular project language.
- [x] The documented scope is limited to verified-core capabilities and the two bounded Search JSON endpoints.
- [x] The report plan includes architecture and ER diagrams with retained source files.
- [ ] Confirm the Class Diagram terminology in the native VPP and export a matching PNG.
- [x] Exactly ten screenshot entries are documented, including two 320x800 mobile captures and a safe 404 response.
- [x] Third-party software is distinguished from first-party application assets.
- [ ] Run and record current read-only repository validation against the final local commit.
- [ ] Open the report and update fields, table of contents, and page numbers after final edits.

## Archive And Privacy Checks

- [ ] Run `composer run audit:cleanup` and the existing source-package process for the final release commit.
- [ ] Inspect `GuideMyPC-Source.zip` for its required root structure and prohibited content.
- [ ] Validate a clean extraction only if the required disposable database environment is available.
- [ ] Assemble `GuideMyPC-Submission.zip` with exactly `Readme.docx`, `GuideMyPC-Final-Report.docx`, `GuideMyPC.vpp`, and `GuideMyPC-Source.zip`.
- [ ] Open every outer-archive artifact and verify that no extra file exists.
- [ ] Perform human review and complete the human signature/date fields if required by the course.

## Final Decision

| Decision | Assembler signature/date | Independent reviewer signature/date |
| --- | --- | --- |
| Human review required before submission |  |  |
