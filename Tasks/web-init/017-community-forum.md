# Task: Community Forum

- Status: Not started
- Priority: High
- Release: R4
- Dependencies: `002-security-bootstrap.md`, `010-authentication-and-user-profile.md`, `016-secure-file-uploads.md`

## Objective

Create a moderated support forum where account holders can ask clear questions, provide answers, vote on helpful content, and identify accepted solutions.

## Current State

The prototype supports posts, comments, and likes. It lacks question/answer semantics, accepted solutions, categories/tags, reports, edit history, moderation states, spam controls, safe attachments, and robust authorization.

## Scope

- Model questions and answers distinctly.
- Add platform/category/tags, problem status, accepted answer, helpful votes, views, and related content.
- Allow question owners to accept one eligible answer and moderators to correct abuse with audit history.
- Support secure screenshot/log attachments from task `016`.
- Add reporting reasons, moderation queue, hidden/locked states, and user notices.
- Add rate limits, duplicate-question suggestions, spam controls, and account-age restrictions where measured abuse requires them.
- Support edit windows/history and clear attribution.
- Integrate published questions into search and AI escalation.

## Non-Goals

- Private messaging
- Real-time chat
- Full reputation economy
- Anonymous posting
- Unmoderated executable/log archive sharing

## Implementation Steps

1. Migrate posts/comments/likes to question, answer, and vote semantics without losing valid content.
2. Build browse, filter, question, ask, answer, edit, vote, accept, report, and moderation flows.
3. Add duplicate suggestions using universal search.
4. Integrate safe attachments and sanitize rendered content.
5. Add notification-ready events while keeping email notifications optional for MVP.
6. Add community guidelines and moderator operating procedures.
7. Record resolution and response-time metrics.

## Database Changes

- Add or migrate questions, answers, votes, accepted-answer references, tags, reports, moderation events, and revisions.
- Add unique vote and accepted-answer constraints.
- Index public state, activity, tags, ownership, and unanswered status.

## Security and Privacy

- Require accounts for posting and voting.
- Enforce owner/moderator permissions server-side.
- Sanitize content, protect mutations with CSRF, rate-limit abuse, and prevent vote manipulation.
- Warn users not to post personal information, passwords, license keys, or private logs.
- Hidden content must not leak through search, AI retrieval, feeds, or direct routes.

## Accessibility

Question/answer hierarchy must be semantic, vote controls need accessible names and state, accepted solutions need text and icon treatment, editor errors must be announced, and moderation status cannot rely on color alone.

## Affected Files

- `community.php`
- `toggle_like.php`
- community delete/admin routes
- new question/answer/report/vote endpoints
- community migrations and search/AI integrations
- CSS and JavaScript for forum interactions

## Acceptance Criteria

- [ ] Account users can ask, answer, edit within policy, vote, report, and accept a solution.
- [ ] Only the question owner or authorized moderator can accept an eligible answer.
- [ ] Users cannot vote repeatedly or on ineligible content according to policy.
- [ ] Hidden/reported content follows moderation rules and does not leak into search/AI.
- [ ] Attachments use the secure upload lifecycle.
- [ ] Duplicate-question suggestions appear before submission.
- [ ] Existing valid community content has a documented migration outcome.

## Validation

- Test guest/user/owner/moderator/admin permissions for every mutation.
- Test duplicate votes, accepted-answer changes, reports, locks, hidden content, edits, and attachment authorization.
- Attempt stored XSS, CSRF, spam bursts, and direct access to moderated content.
- Run keyboard and screen-reader checks on ask, answer, vote, accept, and report flows.

## Definition of Done

The community offers a structured, searchable escalation path with enforceable moderation and clear solved-question outcomes.
