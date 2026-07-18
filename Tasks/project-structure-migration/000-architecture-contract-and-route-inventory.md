# Task: Architecture Contract and Route Inventory

- Status: In progress
- Priority: Critical
- Release: M0
- Dependencies: None

## Objective

Create the authoritative behavioral and structural baseline required to reorganize GuideMyPC without accidentally changing public routes, security behavior, data visibility, or operational assumptions.

## Current State

The repository exposes approximately 54 page and action scripts from its root. Those scripts mix request handling, session state, authorization, SQL, redirects, JSON responses, and rendering. Existing route names are embedded in forms, JavaScript, navigation, email links, canonical metadata, the sitemap, and documentation.

The proposed feature-oriented structure is not yet documented as an enforceable project convention. `docs/application-conventions.md` and the system overview describe the current procedural layout, while `AGENTS.md` has no project-structure reference.

## Scope

- Inventory every web-addressable PHP script and classify it as HTML, JSON, XML, redirect, or mixed response.
- Record allowed HTTP methods, input fields, authentication, authorization, CSRF, rate-limit, session, redirect, status, and database contracts.
- Record current behavior separately from desired corrections where behavior is inconsistent or unsafe.
- Define current, transitional, and target application structures.
- Create `docs/project-structure.md` as the durable structural guide.
- Add a concise reference to the guide and this roadmap in `AGENTS.md`.
- Resolve or formally block policy decisions that affect later feature boundaries.
- Establish architecture decision records or linked decision notes for material choices.

## Non-Goals

- Moving application files
- Adding Composer or namespaces
- Changing URLs or route behavior
- Correcting product behavior while documenting it
- Redesigning the database schema
- Selecting a PHP framework, ORM, or template engine

## Route Contract

The route inventory must record at least:

- Legacy path and canonical path
- Allowed HTTP methods
- Query, form, and JSON fields
- HTML, JSON, XML, or redirect response type
- Success, validation, authentication, authorization, CSRF, rate-limit, and not-found statuses
- Redirect destination and whether HTTP 303 is required
- Guest, user, moderator/editor, and administrator outcomes
- Session keys read or written
- Database tables read or written
- Side effects such as view counting, search telemetry, activity, flash data, or guest progress merging
- JavaScript, form, email, sitemap, and navigation callers
- Current test coverage and required characterization coverage

## Required Decisions

1. Select the canonical Community model: active legacy post/comment tables or the currently unwired question/answer schema.
2. Define public Download eligibility using publication, review state, URL scheme, and trusted-host rules.
3. Select a production Composer strategy: install dependencies after deployment or build an artifact containing `vendor/`.
4. Define trusted-proxy and HTTPS detection requirements before secure-cookie behavior is refactored.
5. Confirm that legacy `*.php` URLs remain canonical through public-root migration; clean URL redirects remain deferred.
6. Confirm private runtime storage stays outside the repository and define required ownership and permissions.

Unresolved decisions must identify an owner, decision deadline, affected tasks, and safe default. Tasks `006` and `008` cannot proceed through their gates while relevant decisions remain open.

## Implementation Steps

1. Build the route inventory from root PHP entry points and verify it against forms, JavaScript, shared navigation, email generation, sitemap output, and redirects.
2. Capture representative baseline responses for public, authenticated, administrator, HTML, JSON, XML, and error paths.
3. Document session keys and the existing `ok`, `data` or `error`, and `meta.request_id` JSON format.
4. Document the current bootstrap, database, error, storage, and request flow.
5. Define target directory responsibilities and allowed dependency directions.
6. Identify active features, foundation-only code, and explicitly deferred modules.
7. Record required decisions and obtain approval or a documented block.
8. Create `docs/project-structure.md` with migration-safe coding conventions.
9. Add a short `AGENTS.md` section linking the structural guide and this roadmap.
10. Update contradictory architecture documentation to distinguish current and target states without claiming unimplemented work.

## Database Changes

None. Existing tables and migration checksums are documented but not modified.

## Security and Privacy

- Do not place secrets, reset tokens, session identifiers, private content, or personal data in captured route fixtures.
- Characterize CSRF exceptions explicitly; do not apply a broad rule that silently breaks telemetry or mixed-response routes.
- Record browser-safe errors rather than internal exception text.
- Treat current publication inconsistencies as decision inputs, not implicit migration behavior.

## Accessibility

The baseline must include representative page titles, landmarks, focus behavior after validation, flash messages, and accessible error responses so later view changes can be compared meaningfully.

## Affected Files

- new route contract documentation under `docs/`
- `docs/project-structure.md`
- `docs/application-conventions.md`
- `docs/submission/system-overview.md`
- architecture diagrams where applicable
- `AGENTS.md`
- this roadmap and downstream task notes

## Rollback Strategy

This task changes documentation only. Revert inaccurate documentation while preserving recorded evidence and decisions in project history.

## Acceptance Criteria

- [ ] Every web entry script has a complete route-contract record.
- [ ] Route callers in PHP, JavaScript, forms, email links, sitemap output, and navigation have been cross-checked.
- [x] Existing behavior and desired behavior are clearly distinguished.
- [x] Session, CSRF, redirect, JSON, error, and publication contracts are documented.
- [x] Community, Downloads, Composer deployment, proxy handling, URL compatibility, and private-storage decisions are approved or explicitly blocked.
- [x] `docs/project-structure.md` defines current, transitional, and target structures and dependency rules.
- [x] `AGENTS.md` links to the structural guide and migration roadmap without duplicating them.
- [x] Foundation-only AI, maintenance, uploads, confidence, and community code is not represented as a completed module.
- [ ] Downstream tasks can identify exactly which contracts they must preserve.

## Validation

- Compare the route inventory with all root PHP scripts and Apache access rules.
- Search route names across PHP, JavaScript, robots, sitemap, email, and documentation references.
- Manually exercise representative guest, user, administrator, JSON, XML, wrong-method, unauthenticated, unauthorized, invalid-CSRF, and not-found paths.
- Review `docs/project-structure.md` against this roadmap and the implemented repository, marking target-only statements explicitly.
- Confirm all required decisions have an owner and status.

## Definition of Done

The current application contracts and target structural rules are approved, linked from contributor instructions, and precise enough that task `001` can change initialization without relying on undocumented behavior.

## Implementation Evidence

- `docs/project-structure.md` records the current, transitional, and target structures, dependency rules, responsibilities, storage boundary, and decision register.
- `docs/route-contracts.md` records legacy route groups, shared response/session contracts, known policy differences, and characterization requirements.
- `AGENTS.md`, `docs/application-conventions.md`, and `docs/submission/system-overview.md` now reference the migration accurately without claiming the target structure is already implemented.
- The project owner approved the active legacy Community model and a public Download rule requiring publication, approval, and a safe HTTPS URL; `docs/project-structure.md` and `docs/route-contracts.md` record both decisions.
- Remaining work: create route-by-route characterization evidence and cross-check every caller.
