# Task: Knowledge Base

- Status: Completed
- Priority: High
- Release: R1
- Dependencies: `005-responsive-design-system-and-layout.md`, `007-universal-search.md`

> **Final-release scope notice (2026-07-22):** Public read-only Knowledge remains in final scope. Editorial forms, Knowledge CRUD, and Knowledge administration are deferred; this task's completed public-read validation does not prove or require them. Apply the superseding scope addendum in `Tasks/final-project-mvp/README.md`.

## Objective

Create a searchable, maintainable knowledge base for technical explanations that are broader or shorter than repair guides.

## Current State

The database models categories and guides only. There are no first-class articles, error-code records, FAQs, glossary terms, tags, publication workflow, or editorial review metadata.

## Scope

- Support article types: explanation, error code, FAQ, glossary, maintenance, security, hardware, software, and networking.
- Add platform/category/tag relationships, unique slugs, summaries, body content, source references, and related resources.
- Add draft, review, published, and archived states with author/reviewer timestamps.
- Build index, category, article, glossary, and error-code views.
- Add related articles, guides, diagnostics, videos, and official references.
- Add structured metadata where appropriate.
- Define a content style guide covering beginner language, warnings, sources, and freshness reviews.

## Non-Goals

- Public wiki editing
- Arbitrary unreviewed HTML
- AI-generated automatic publishing
- Full multilingual content in MVP

## Implementation Steps

1. Design normalized article, tag, relation, and source tables.
2. Select a constrained content format and safe rendering strategy.
3. Build public listing/detail routes with publication checks.
4. Add editorial forms through task `018` conventions.
5. Integrate documents into universal search and related-content components.
6. Add review dates and visible “last reviewed” information.
7. Seed representative error codes, FAQs, and glossary entries for each primary platform.

## Database Changes

- Add knowledge articles, tags, article-tag links, sources, and related-content records.
- Add publication state, author/reviewer, and review timestamps.
- Add indexes for slugs, types, categories, states, and search.

## Security and Privacy

Sanitize or safely render stored content, validate official-source URL schemes, preserve revision accountability, and prevent drafts from leaking through search or direct URLs.

## Accessibility

Articles use semantic headings, descriptive links, accessible tables, alt text for meaningful images, understandable warnings, and definitions that do not depend on hover.

## Affected Files

- new knowledge-base public pages and includes
- database migrations and seeds
- universal search integration
- admin/content files implemented in task `018`
- CSS for article and glossary presentation

## Acceptance Criteria

- [x] Users can browse and search each required article type.
- [x] Draft and archived content is unavailable to public users.
- [x] Every published article shows platform, review date, sources, and related help where applicable.
- [x] Content rendering cannot execute stored scripts.
- [x] Error-code URLs are stable and exact-code searches resolve correctly.
- [x] Editorial guidance requires plain language and safety warnings.

## Validation

- Create one item of each type and test draft-to-published lifecycle.
- Attempt direct and search access to drafts.
- Test unsafe markup and URL schemes.
- Run accessibility checks against representative long-form, FAQ, glossary, and error-code pages.

Validation completed locally: clean and prototype database migrations/seeds created representative Windows, macOS, Linux, Android, iPhone/iPad, and Wi-Fi records, including an exact `0x0000007B` error-code reference. Public knowledge index, article, glossary, exact error-code, universal search, and article suggestions returned HTTP 200; a missing article returned 404. The focused integration test temporarily changed a seeded article to draft in a rolled-back transaction and confirmed both direct lookup and search exclude it. Helper tests confirm stored markup is escaped and unsafe URL schemes are rejected. PHP lint, existing search tests, and `git diff --check` passed.

## Definition of Done

GuideMyPC has a reviewed, searchable reference layer that supports search, guides, diagnostics, and AI recommendations.
