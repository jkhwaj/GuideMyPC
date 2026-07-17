# Task: Search, Home, Sitemap, and Cross-Feature Reads

- Status: In progress
- Priority: High
- Release: M4
- Dependencies: `006-downloads-community-and-feature-admin.md`

## Objective

Migrate the cross-feature read paths after their source features stabilize, using explicit read models rather than feature-to-feature coupling or generic repositories.

## Current State

Home, Search, Sitemap, and the administrator dashboard query several product areas directly. Search also records aggregate events and exposes JSON suggestions and selection telemetry. These routes depend on publication rules and legacy filenames across Guides, Knowledge, Downloads, and Community.

## Scope

- Create explicit Home, Search, Sitemap, and dashboard read models.
- Preserve search query normalization, filters, pagination, suggestions, event recording, and response formats.
- Preserve homepage section ordering, limits, empty states, and publication rules.
- Preserve sitemap XML format, content type, paths, and publication filtering.
- Use feature-approved publication policies without making features depend on one another.
- Migrate remaining cross-feature views and root routes to thin dispatchers.
- Preserve the approved non-CSRF telemetry contract or implement an explicitly approved replacement.

## Non-Goals

- Introducing a search service such as Meilisearch
- Changing ranking or analytics policy without separate approval
- Adding clean URLs
- Creating a shared generic repository for all content types
- Moving feature-owned mutations into aggregator modules
- Redesigning the homepage or administrator dashboard

## Implementation Steps

1. Characterize Home, Search, suggestions, telemetry, Sitemap, and dashboard outputs and side effects.
2. Define read-model interfaces around feature-approved public projections.
3. Extract Search query composition, result mapping, suggestions, and event recording.
4. Extract Home sections using named read dependencies.
5. Extract Sitemap URL generation using named routes and explicit metadata.
6. Extract the administrator dashboard as a read-only operational projection.
7. Replace route implementations with compatibility dispatchers.
8. Add tests for publication consistency across every aggregator.
9. Verify no aggregator can bypass feature authorization or publication policies.

## Database Changes

No intended schema changes. Search index or telemetry corrections require separately reviewed forward migrations and data-retention consideration.

## Security and Privacy

- Search telemetry must remain aggregate, bounded, rate-limited, and free of unnecessary personal or sensitive query data.
- Suggestions and aggregators must not expose draft, pending, private, or unauthorized records.
- Sitemap output must contain only approved public canonical URLs.
- Dashboard projections require administrator authorization and must not expose secrets.

## Accessibility

- Preserve search labels, filter controls, result announcements, keyboard navigation, suggestion semantics, empty states, and pagination.
- Homepage and dashboard heading hierarchy and landmarks must remain coherent.

## Affected Files

- `app/Features/Search/`
- `app/Features/Home/`
- Sitemap and dashboard read-model code
- corresponding views
- `index.php`
- `search.php`
- `search_suggestions.php`
- `search_event.php`
- `sitemap.php`
- `admin.php`
- `includes/search.php`
- search, publication, sitemap, Home, and dashboard tests

## Rollback Strategy

Keep each aggregator behind its legacy route. Roll back the route to its prior query composition without reverting source-feature modules. Search telemetry changes must preserve event compatibility or include a documented data transition.

## Acceptance Criteria

- [ ] Search, Home, Sitemap, and dashboard use explicit cross-feature read models.
- [ ] No aggregator writes directly to another feature's state except through an approved named service.
- [ ] Publication rules match the owning features across all projections.
- [ ] Search HTML, JSON suggestions, telemetry, filters, pagination, and ranking remain compatible.
- [ ] Sitemap XML, content type, canonical paths, and public-only filtering remain compatible.
- [ ] Homepage and dashboard section behavior matches the route contract.
- [ ] Aggregators introduce no circular feature dependencies.
- [ ] Root scripts contain only compatibility dispatch and route-specific adaptation.

## Validation

- Compare Search results, ranking, filters, pagination, suggestions, telemetry statuses, and rate limits across representative datasets.
- Verify Home and Sitemap never include draft, pending, unpublished, unsafe, or unauthorized records.
- Validate sitemap XML and generated absolute URLs under hostname and `/GuideMyPC/` subdirectory configurations.
- Test administrator dashboard authorization and failure-before-output behavior.
- Run no-result, partial-data, unavailable-database, and large-result cases.
- Run accessibility checks, PHP lint, and the full fast verification suite.

## Definition of Done

All cross-feature read paths use named, policy-consistent projections, remain compatible at their legacy routes, and no longer embed unrelated feature SQL in page scripts.

## Implementation Evidence

- Characterization found duplicated Download and Community publication rules. Task `006` now enforces the approved policy in current public projections; task `007` must retain it while replacing remaining cross-feature SQL with named read models.
- Extracted pure Search query normalization and aggregate-telemetry safety checks into `GuideMyPC\Features\Search\SearchQuery`, retaining existing helper delegates and the route's current HTML/JSON/telemetry behavior.
- Home, Sitemap, dashboard, Search ranking/filtering, suggestions, event recording, and the approved non-CSRF telemetry exception remain unchanged in this increment.
- Added helper coverage for direct namespaced normalization of overlength input.
