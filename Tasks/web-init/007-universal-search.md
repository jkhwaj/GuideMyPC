# Task: Universal Search

- Status: Not started
- Priority: Critical
- Release: R1
- Dependencies: `003-database-migrations-and-seeds.md`, `004-application-structure-and-error-handling.md`, `006-homepage-and-navigation.md`

## Objective

Provide fast, forgiving search across the trusted GuideMyPC content types, with clear filters and useful recovery from misspellings or zero results.

## Current State

Search is limited to guide text within a selected category. The homepage field has no form behavior, and there is no all-content result model, autocomplete, typo handling, or search-success measurement.

## Scope

- Search published guides, knowledge articles, error codes, diagnostics, downloads, videos, and community questions.
- Build one results page with type, platform, difficulty, recency, and safety-relevant filters.
- Add autocomplete for approved titles, error codes, categories, and common queries.
- Normalize whitespace/case, handle basic spelling variants, and offer related searches.
- Rank exact title/error-code matches before broader text relevance.
- Provide safe excerpts with highlighted terms and no untrusted HTML.
- Record privacy-conscious aggregate query outcomes and zero-result events.
- Define a future Meilisearch migration threshold based on measured needs.

## Non-Goals

- Personalized ranking in the first version
- Web-wide search
- Sending every query to an AI provider
- Elasticsearch or Meilisearch before measurement justifies it

## Implementation Steps

1. Define a common searchable-document contract for each content type.
2. Add MariaDB full-text indexes where appropriate and deterministic fallback matching.
3. Implement a search service that returns normalized result records.
4. Build paginated result and filter UI with query parameters.
5. Add a rate-limited suggestion endpoint with minimum query length and result caps.
6. Add typo aliases and curated related queries for high-value support terms.
7. Record result count, selected result type, and zero-result state without storing sensitive raw text longer than necessary.

## Database Changes

- Add full-text/search indexes to published content.
- Add curated search aliases and related-query mappings.
- Add aggregate search-event storage with a retention policy, if analytics tooling is not yet available.

## Security and Privacy

- Use prepared statements and escaped snippets.
- Rate-limit autocomplete and cap query size.
- Do not expose drafts, private diagnostics, private uploads, or restricted community content.
- Avoid retaining queries likely to contain personal information; document retention and redaction.

## Accessibility

Autocomplete must follow the ARIA combobox pattern, support keyboard selection and dismissal, announce result counts, and remain optional to basic form submission.

## Affected Files

- `index.php`
- `guides.php`
- new search results and suggestion endpoints
- shared search service/helpers
- content migrations and indexes
- CSS/JavaScript for suggestions and filters

## Acceptance Criteria

- [ ] A single query can return clearly labeled results from all published content types.
- [ ] Exact error-code and title matches rank predictably.
- [ ] Filters are shareable in the URL and survive pagination.
- [ ] Empty and misspelled queries offer useful alternatives.
- [ ] Autocomplete works with keyboard and does not expose restricted content.
- [ ] Typical local seeded queries meet the agreed response-time budget.

## Validation

- Test a query matrix covering exact, partial, misspelled, error-code, punctuation, empty, very long, and malicious input.
- Verify draft/private content cannot appear.
- Measure search and suggestion latency against representative seeded data.
- Run keyboard and screen-reader tests on autocomplete and filters.

## Definition of Done

Users can find a relevant trusted next step from natural problem descriptions without knowing the correct technical term.
