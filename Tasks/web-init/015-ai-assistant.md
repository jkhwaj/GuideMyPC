# Task: AI Assistant

- Status: Not started
- Priority: Critical
- Release: R3
- Dependencies: `007-universal-search.md`, `009-structured-repair-guides.md`, `011-diagnostic-engine.md`, `014-trusted-downloads.md`

## Objective

Provide a conversational troubleshooting assistant that asks focused follow-up questions and recommends reviewed GuideMyPC resources while applying strict technical-safety controls.

## Current State

`ai.php` is empty. There is no provider integration, conversation storage, retrieval pipeline, prompt policy, rate limiting, cost control, streaming, screenshot analysis, or safety evaluation.

## Scope

- Add a provider-neutral server-side AI client and configuration.
- Support guest-limited and signed-in conversations with session context.
- Retrieve only published guides, knowledge articles, diagnostics, approved videos, and trusted downloads for recommendations.
- Ask clarifying questions before high-impact advice and distinguish likely software versus hardware causes.
- Include citations/links to the reviewed resources used in an answer.
- Warn before data loss, firmware, registry, account reset, command-line, or disassembly steps.
- Recommend backups and official manufacturer resources where appropriate.
- Escalate to a diagnostic flow or community question when confidence is insufficient.
- Add request, token/cost, timeout, retry, moderation, and daily usage limits.
- Provide clear disclosure that answers may be incorrect and do not represent remote device inspection.

## Non-Goals

- Autonomous command execution
- Training a custom foundation model
- Unreviewed internet browsing
- AI-only answers for high-risk procedures
- Screenshot handling before task `016`

## Implementation Steps

1. Define provider interface, supported models, environment settings, timeouts, and budget limits.
2. Build retrieval from published GuideMyPC content with access-state filters.
3. Define system safety policy and structured response fields for explanation, questions, warnings, citations, and escalation.
4. Implement conversation start/message/history endpoints with server-side authorization and rate limits.
5. Build accessible chat UI with non-streaming fallback, cancel/retry, error states, and session memory.
6. Add safety and recommendation evaluations for representative support scenarios.
7. Add aggregate resolution/escalation metrics and user feedback.

## Database Changes

- Add conversations, messages, cited resources, usage accounting, feedback, and safety-event records.
- Set explicit retention and deletion policies.
- Store provider identifiers and token counts without storing API secrets.

## Security and Privacy

- Keep provider keys server-side.
- Treat all user and retrieved content as untrusted against prompt injection.
- Do not include private uploads or conversations in another user's context.
- Redact sensitive logs and disclose what data is sent to the provider.
- Enforce output safety in application code; prompts alone are insufficient.
- Never recommend unapproved download destinations.

## Accessibility

Messages use semantic transcript markup, status updates are announced without excessive interruption, controls are keyboard accessible, focus is managed predictably, and users can access citations outside dynamic chat.

## Affected Files

- `ai.php`
- new AI service, retrieval, policy, and action endpoints
- AI migrations
- account/profile conversation views
- rate-limit, logging, CSS, and JavaScript integrations

## Acceptance Criteria

- [ ] The assistant asks clarifying questions and maintains context within a session.
- [ ] Recommendations cite only published/approved GuideMyPC resources.
- [ ] High-risk advice includes appropriate warnings and safer alternatives.
- [ ] Provider failures, timeouts, quota exhaustion, and moderation blocks fail safely.
- [ ] Guest and account limits are enforced server-side.
- [ ] Users can delete saved conversations according to the retention policy.
- [ ] AI cannot expose another user's content or server secrets.

## Validation

- Run scenario tests for slow PC, blue screen, no power, Wi-Fi failure, malware, password recovery, disk failure, and ambiguous errors.
- Test prompt injection, requests for unsafe downloads, destructive commands, secret extraction, and cross-user conversation IDs.
- Simulate provider timeout, malformed response, quota failure, and disabled AI configuration.
- Review keyboard and screen-reader chat behavior.

## Definition of Done

The AI provides bounded, source-linked troubleshooting assistance that improves navigation without becoming an unreviewed authority or unsafe download channel.
