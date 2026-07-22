# Task: Diagnostic Engine

- Status: Not started
- Priority: Critical
- Release: R2
- Dependencies: `009-structured-repair-guides.md`, `010-authentication-and-user-profile.md`

> **Final-release scope notice (2026-07-22):** This file is retained as historical planning. AI escalation is excluded, and any Community reference is limited to the canonical legacy posts/comments/likes model rather than Community v2. Apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Build an interactive, data-driven decision-tree tool that asks understandable questions and recommends safe next steps based on the user's answers.

## Current State

There is no diagnostic data model, runtime, authoring workflow, saved diagnostic state, or result explanation. The desired flagship flow currently exists only in the PRD example.

## Scope

- Support published diagnostic flows by platform and symptom.
- Model question nodes, answer options, branch rules, evidence weights, terminal outcomes, and related resources.
- Support yes/no, single-choice, and simple observed-value questions; avoid arbitrary code rules.
- Show progress, allow backtracking/restarting, and preserve answers during the session.
- Save and resume sessions for signed-in users.
- Recommend guides, knowledge articles, videos, downloads, and AI/community escalation.
- Display risk, time, required tools, and backup warnings before recommended actions.
- Provide an admin preview and validation process through task `018`.

## Non-Goals

- Automatic hardware scanning
- Medical-style certainty claims
- AI-generated branches at runtime
- Executing repair commands on the user's device

## Implementation Steps

1. Define versioned flow, node, option, evidence, outcome, and resource-link structures.
2. Build a validator that rejects missing nodes, broken links, unreachable outcomes, and cycles unless deliberately bounded.
3. Implement server-authoritative session state with progressive JavaScript enhancement.
4. Add start, answer, back, resume, restart, and complete routes.
5. Build accessible question and result views.
6. Seed the “PC will not turn on” reference flow and representative flows for other primary platforms.
7. Record completion and usefulness metrics without exposing answer details unnecessarily.

## Database Changes

- Add diagnostic flows, versions, nodes, options, evidence, outcomes, and resource links.
- Add diagnostic sessions and answers with ownership and expiry.
- Index published flow slugs, active versions, session ownership, and branch lookups.

## Security and Privacy

- Treat answers as potentially sensitive device information.
- Authorize saved sessions by owner, expire abandoned guest sessions, and avoid public sequential identifiers.
- Validate every transition server-side rather than trusting posted next-node IDs.
- Do not render admin-authored executable markup.

## Accessibility

Questions use fieldsets and legends, progress is announced textually, back/restart controls are unambiguous, focus moves to the new question, and results do not rely on charts or color alone.

## Affected Files

- new diagnostic public routes and shared engine
- database migrations and seeds
- user profile saved-session integration
- search integration
- admin flow editor/preview in task `018`
- CSS/JavaScript for progressive interactions

## Acceptance Criteria

- [ ] Users can complete, backtrack, restart, and resume a diagnostic flow.
- [ ] The server rejects tampered or invalid transitions.
- [ ] Broken or unpublished flows cannot be started publicly.
- [ ] Results explain observed evidence and link to safe relevant resources.
- [ ] Signed-in users can revisit saved diagnostics; guests retain short-lived session progress.
- [ ] The reference no-power/display flow covers the PRD question path.

## Validation

- Unit-test graph validation, branching, backtracking, and terminal outcomes.
- Test tampered node/option IDs and cross-user session access.
- Traverse every seeded path and verify no dead ends.
- Test with JavaScript disabled and with keyboard/screen reader.

## Definition of Done

The diagnostic tool reliably narrows a symptom to explainable likely causes and safe resources without pretending to inspect the device directly.
