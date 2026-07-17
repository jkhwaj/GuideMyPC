# Task: Knowledge and Guide Read Models

- Status: In progress
- Priority: High
- Release: M3
- Dependencies: `003-view-system-and-static-pages.md`

## Objective

Migrate the first database-backed vertical slices by extracting Knowledge and guide read/query behavior into feature-owned code while preserving legacy URLs, publication rules, metadata, and rendering.

## Current State

Knowledge and guide routes combine request parsing, SQL, publication checks, related content, metadata, and HTML. Guide detail also triggers view, activity, progress, favorite, and rating-related side effects that must remain stable while read queries are extracted.

## Scope

- Create feature boundaries for Knowledge and Guides.
- Extract knowledge listings, article detail, glossary, and error-code queries.
- Extract guide/category listing and guide detail read models.
- Move the corresponding HTML into feature view directories.
- Preserve existing filters, pagination, ordering, publication checks, not-found behavior, metadata, and links.
- Keep guide-side commands behind compatibility calls until task `005` migrates them.
- Define query result shapes explicitly rather than exposing raw database state to views.

## Non-Goals

- Migrating guide progress, favorites, ratings, guest merging, or account behavior
- Changing publication policy or content schema
- Introducing generic repositories for unrelated features
- Rewriting SQL with an ORM
- Migrating Search, Home, Sitemap, or dashboard aggregation
- Renaming public routes

## Implementation Steps

1. Characterize all Knowledge and guide read routes against the route matrix.
2. Define narrowly scoped query/repository objects around existing prepared SQL.
3. Extract Knowledge routes and views first.
4. Extract guide/category listing and filtering.
5. Extract guide detail content, steps, tools, sources, related knowledge, and rating summary reads.
6. Bridge existing guide detail side effects through named compatibility calls without changing their timing.
7. Pass explicit read-model data to views.
8. Reduce migrated root scripts to request parsing and controller dispatch.
9. Add deterministic integration fixtures that do not skip when seed content is missing.

## Database Changes

No intended schema changes. If a query defect requires schema correction, create a separately reviewed forward migration; never edit an applied historical migration.

## Security and Privacy

- Preserve publication and not-found behavior exactly unless task `000` approved a separate correction.
- Use prepared statements for all request-derived values.
- Do not expose drafts, administrative fields, internal source metadata, or another user's state.
- Keep guide side effects authorized and bounded during the transitional split.

## Accessibility

- Preserve semantic article structure, breadcrumb labels, warning presentation, step order, table semantics, print behavior, and keyboard-accessible controls.
- Maintain understandable empty and not-found states.

## Affected Files

- new code under `app/Features/Knowledge/`
- new code under `app/Features/Guides/`
- `resources/views/knowledge/`
- `resources/views/guides/`
- Knowledge and guide root routes
- relevant files under `includes/knowledge.php` and `includes/guides.php`
- deterministic Knowledge and Guides integration tests
- route-contract evidence

## Rollback Strategy

Migrate routes one at a time behind their existing filenames. A route can return to its prior SQL/rendering implementation independently while extracted read objects remain available to completed routes.

## Acceptance Criteria

- [ ] All migrated routes retain their legacy path, method, inputs, statuses, and canonical metadata.
- [ ] Knowledge and Guides own their read SQL and result mapping.
- [ ] Views receive explicit read models and contain no SQL.
- [ ] Publication, filtering, ordering, pagination, not-found, and empty-state behavior matches the approved contract.
- [ ] Guide view/activity and user-state side effects occur at the same point and frequency as before migration.
- [ ] No cross-feature generic repository or ORM is introduced.
- [ ] Tests provision required data deterministically and do not report success after skipping core assertions.
- [ ] Root scripts for migrated routes are thin compatibility dispatchers.

## Validation

- Run fresh migrations and deterministic Knowledge and Guides fixtures in an isolated test database.
- Compare list, filtered, detail, unpublished, missing, empty, and pagination responses before and after extraction.
- Verify guide steps, tools, sources, warnings, related content, ratings, metadata, and print output.
- Confirm view counting, activity, and guest/user state side effects have not duplicated or disappeared.
- Run accessibility checks at desktop and 320px widths with JavaScript disabled.
- Run PHP lint and the full fast verification suite.

## Definition of Done

Knowledge and guide read behavior is feature-owned, rendered through the new view boundary, covered by deterministic integration tests, and still reachable through every documented legacy route contract.

## Implementation Evidence

- Extracted the published Knowledge article lookup into `GuideMyPC\Features\Knowledge\KnowledgeRepository`.
- `knowledge_published_article()` remains a compatibility delegate, preserving `knowledge_article.php` and existing integration-test callers while the remaining Knowledge queries are characterized.
- No publication policy, route, rendering, Guide behavior, or database schema changed in this increment.
- The current Knowledge integration test still depends on seeded content and may report `SKIP`; deterministic fixture work remains required before this task can complete.
- PHP lint passed for the repository, compatibility helper, article route, and Knowledge integration test. `tests/helpers_test.php` and `tests/knowledge_integration_test.php` pass against the current seeded database.
