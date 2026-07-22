# Submission Sources

This directory contains tracked, reviewable source material for the final academic submission. Final Word documents and screenshots stay local and ignored by Git. The packaging command overlays them into the generated submission ZIP.

## Tracked review sources

| Source | Supports | Status |
| --- | --- | --- |
| `system-overview.md` | The two-page prose overview | Draft, based on implemented scope |
| `report-outline.md` | The required final-report sections | Draft; requires team and diagram details |
| `test-evidence.md` | Test appendix and release evidence | Release-candidate evidence only |
| `third-party-inventory.md` | Dependency section | Current local environment recorded |
| `screenshots/README.md` | Screenshot appendix | Capture manifest awaiting final release |
| `final-checklist.md` | Final artifact and package sign-off | Template awaiting final release |
| `docs/team/README.md` | Separate team Readme source | Requires final review before transfer to Word |
| `scripts/package-source.ps1` | Standalone source archive | Optional supporting artifact |

## Local final materials

Place the instructor-facing files in these ignored paths:

```text
docs/submission/
|-- documents/
|   |-- Readme.docx
|   `-- GuideMyPC-Final-Report.docx
`-- screenshots/
    `-- 8 to 10 final PNG, JPG, or WebP images

uml/
|-- source/
|   `-- GuideMyPC.vpp
`-- exports/
    |-- use-case.png or .pdf
    |-- class-diagram.png or .pdf
    |-- activity-diagram.png or .pdf
    `-- state-machine.png or .pdf
```

Do not commit personal contact information, final Word files, Visual Paradigm projects, screenshots, or generated ZIPs. Keep them in the ignored folders above and back them up outside the repository.

## Package commands

Build a review ZIP before every final artifact is ready:

```powershell
composer run package:submission
```

Run the strict final gate after the Word files, four UML diagrams, and 8 to 10 screenshots are present:

```powershell
composer run package:submission:strict
```

Both commands create `build/GuideMyPC_Submission.zip`. The ZIP contains the course-facing `frontend/`, `backend/`, `database/`, `uml/`, and `docs/` folders while preserving a runnable source tree under `backend/`.
