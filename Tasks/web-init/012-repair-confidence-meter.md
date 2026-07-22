# Task: Repair Confidence Meter

- Status: Not started
- Priority: High
- Release: R2
- Dependencies: `011-diagnostic-engine.md`

> **Final-release scope notice (2026-07-22):** This file is retained as historical planning. AI escalation and Community-v2 escalation are excluded and are not final-release acceptance criteria. Apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Rank likely causes from diagnostic evidence and explain why each cause is suggested, while avoiding false precision or unsafe certainty.

## Current State

No confidence model exists. The PRD proposes percentage examples, but an unexplained percentage could mislead users into treating an estimate as a confirmed diagnosis.

## Scope

- Calculate deterministic scores from reviewed evidence weights attached to answers and outcomes.
- Normalize scores for display while retaining the raw evidence basis for debugging and audit.
- Use plain-language bands such as High, Moderate, and Low confidence alongside any numeric score.
- Explain supporting and conflicting observations.
- Show repair time, difficulty, tools, risk, backup requirements, and recommended next action.
- Provide “none of these fit” and escalation options.
- Store algorithm/version information with completed results.

## Non-Goals

- Machine-learning diagnosis in MVP
- Claiming component failure without physical testing
- Presenting percentages without explanation
- Automatically suppressing safety warnings because confidence is high

## Implementation Steps

1. Define scoring, normalization, tie-breaking, minimum-evidence, and confidence-band rules.
2. Add outcome-specific reviewed evidence weights.
3. Calculate scores in a pure, unit-tested service.
4. Build an accessible ranked result component with explanations and caveats.
5. Add content-author preview showing how answer combinations affect rankings.
6. Record usefulness feedback and escalations for later calibration.

## Database Changes

- Extend diagnostic evidence/outcomes with signed weights and minimum evidence.
- Save score algorithm version and result snapshot with completed sessions.
- Add non-identifying result feedback fields.

## Security and Privacy

Do not expose internal admin notes or other users' diagnostic data. Avoid using personal identity in scoring. Preserve all guide and diagnostic safety warnings regardless of ranking.

## Accessibility

Rankings must have text labels and explanations; color bars are supplementary. Avoid animated meters, expose values in readable text, and keep caveats adjacent to scores.

## Affected Files

- diagnostic scoring service
- diagnostic result page/components
- diagnostic migrations and admin preview
- automated scoring tests

## Acceptance Criteria

- [ ] The same answers and scoring version always produce the same ranking.
- [ ] Each result explains evidence, uncertainty, time, difficulty, tools, and risk.
- [ ] Low-evidence sessions display uncertainty rather than inflated percentages.
- [ ] Authors can preview and test scoring before publishing a flow.
- [ ] Users can reject the suggestions and reach AI or community escalation.

## Validation

- Unit-test normal, conflicting, tied, missing, and maliciously tampered answer sets.
- Have subject-matter reviewers inspect seeded flow rankings.
- Test textual understanding without colors or visual meter graphics.
- Compare saved result snapshots after a flow version changes.

## Definition of Done

Diagnostic results provide useful, reproducible prioritization with transparent uncertainty and no implication of guaranteed diagnosis.
