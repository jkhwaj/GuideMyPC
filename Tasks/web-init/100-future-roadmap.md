# Task: Post-MVP Roadmap

- Status: Not started
- Priority: Low
- Release: Future
- Dependencies: Successful MVP launch and measured user needs

## Objective

Preserve the Phase 2 and Phase 3 product direction without expanding the MVP release gate or committing to solutions before usage data exists.

## Entry Criteria

- R0-R4 are deployed and stable.
- Success metrics and user research identify the highest-value unresolved problems.
- Security, privacy, accessibility, moderation, and operational capacity are available for the selected feature.
- Each initiative receives its own discovery and implementation task before development.

## Phase 2 Candidates

### Personalized Dashboard

- Continue recent guides, conversations, and diagnostics.
- Show maintenance reminders and saved resources.
- Provide user-controlled device profiles without covert detection.

### Better AI Context and Recommendations

- Improve retrieval quality from measured feedback.
- Summarize prior troubleshooting only with user consent.
- Add stronger evaluation sets by platform and risk category.
- Consider multilingual retrieval only after translated content review exists.

### Community Reputation

- Add reputation only where it improves answer quality and moderation.
- Avoid incentives that reward volume, unsafe certainty, or vote manipulation.
- Keep accepted solutions and staff verification distinct from popularity.

### Expanded Device Support

- Add printers, NAS devices, smart-home products, and consoles based on demand.
- Require content owners and diagnostic coverage before adding navigation categories.

### Preventive Maintenance

- Add user-configurable reminders through email or push channels.
- Require clear opt-in, frequency controls, unsubscribe, and timezone handling.

## Phase 3 Candidates

### Mobile Applications

- Evaluate a responsive web/PWA approach before native Android and iOS apps.
- Reuse documented APIs only after the PHP application has stable service boundaries.
- Include secure session/token storage and mobile accessibility in discovery.

### Voice-Enabled AI

- Add speech input/output only with explicit consent and clear recording/provider disclosure.
- Preserve a complete text alternative.
- Evaluate recognition quality for technical terms and error codes.

### Hardware Detection Tools

- Prefer transparent, open, user-initiated data collection.
- Never require unsigned executables or broad system privileges.
- Define exactly what is collected, retained, uploaded, and deleted.
- Subject any downloadable helper to independent security review and signed releases.

### Remote Desktop and Live Expert Support

- Treat remote access as a separate high-risk product with identity verification, consent, recording/retention, technician vetting, fraud prevention, and insurance/legal review.
- Never ask users to install generic remote-control software through AI chat.

### Internationalization

- Add locale-aware routing and content fallback.
- Translate safety-critical content through qualified human review.
- Support right-to-left layouts and localized search/error-code aliases.

### AI Repair Flowcharts

- Generate only drafts for expert review.
- Run graph validation and safety checks before publication.
- Record model, source content, reviewer, and revision history.

### Browser Extension

- Define a narrow purpose that cannot be met by the website.
- Minimize permissions and avoid reading browsing history or page content by default.
- Publish privacy disclosures and pass extension-store security review.

## Deferred Architecture Evaluation

Reassess the PHP/MariaDB architecture only when measurements show a concrete limitation. Potential future changes include:

- Meilisearch for search quality or scale
- Redis for distributed sessions, queues, caching, or rate limits
- Object storage for private uploads
- Background workers for scans, link checks, email, and AI jobs
- A documented JSON API for mobile clients
- Framework migration only with a feature-by-feature transition and measured benefit

Do not adopt React, Node.js, PostgreSQL, or cloud services solely because they appeared in the original technology recommendations.

## Security and Privacy

Every future initiative requires a threat model, data-flow inventory, retention policy, access model, abuse analysis, and deletion process before implementation.

## Accessibility

Every future interface must preserve WCAG 2.2 AA coverage. Voice, mobile, diagrams, reminders, and hardware tools require equivalent nonvisual and keyboard-accessible paths.

## Acceptance Criteria

- [ ] No candidate is treated as committed without a separate approved task.
- [ ] Prioritization uses measured user value, risk, effort, and operating cost.
- [ ] High-risk features receive legal, privacy, and security discovery before estimates.
- [ ] Architecture changes have measured triggers and migration plans.
- [ ] Deferred PRD capabilities remain visible and traceable.

## Validation

- Review roadmap candidates after each MVP release retrospective.
- Compare proposed work against success metrics, support requests, moderation load, and operating costs.
- Archive or revise candidates when evidence changes.

## Definition of Done

This is a living roadmap. An individual candidate leaves this file only when it has an approved discovery task, owner, success measure, and release target.
