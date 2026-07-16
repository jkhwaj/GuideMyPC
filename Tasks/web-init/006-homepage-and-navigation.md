# Task: Homepage and Navigation

- Status: Not started
- Priority: High
- Release: R1
- Dependencies: `005-responsive-design-system-and-layout.md`

## Objective

Turn the homepage into a clear first step for users who describe a problem, browse by platform, or need a trusted guide, download, maintenance recommendation, or community answer.

## Current State

`index.php` contains a nonfunctional search input, category cards, and links to an empty AI page and a Guides route that fails without a category. The PRD homepage sections and complete footer are not implemented.

## Scope

- Add the product tagline and a prominent semantic search form using “Describe your problem...” language.
- Add a smaller visible AI entry point that does not compete with search.
- Display Windows, macOS, Linux, Android, iPhone/iPad, and Wi-Fi/router categories from published data.
- Put popular problems behind an accessible “Browse Common Problems” disclosure.
- Add curated maintenance, trusted-download, recommended-article, and community-preview sections.
- Complete footer links for About, Contact, Privacy, Terms, Disclaimer, Donate, and approved social channels.
- Fix navigation so Guides has a valid all-guides destination.
- Add useful empty/error states if homepage content cannot load.

## Non-Goals

- Implementing search ranking; owned by task `007`
- Implementing AI; owned by task `015`
- Display advertising
- Auto-playing media

## Implementation Steps

1. Define responsive homepage hierarchy around search as the primary action.
2. Connect search submission to the universal results route.
3. Add configurable curated content queries with safe fallbacks.
4. Add the common-problem disclosure with URL-accessible destinations.
5. Complete informational pages and footer navigation.
6. Add loading, empty, and database-failure behavior.

## Database Changes

Use publication and featured-order fields introduced through migrations where needed. Avoid hard-coding all homepage content in PHP.

## Security and Privacy

Search submission must be a safe GET request with escaped values. External links use reviewed destinations and appropriate `rel` attributes.

## Accessibility

Use one primary heading, a labeled search field, descriptive link text, an accessible disclosure control, ordered heading levels, and no content that depends only on icons.

## Affected Files

- `index.php`
- `guides.php`
- `about.php`
- `contact.php`
- new privacy, terms, disclaimer, and donate pages
- shared navigation/footer files
- CSS and JavaScript for homepage interactions

## Acceptance Criteria

- [ ] The first viewport clearly presents product purpose and functional search.
- [ ] Every primary navigation link reaches a useful page.
- [ ] Common problems are hidden initially but keyboard and screen-reader accessible.
- [ ] All required homepage sections have published-content and empty states.
- [ ] Footer legal and contact links are complete.
- [ ] Homepage remains usable without JavaScript.

## Validation

- Submit normal, misspelled, empty, and special-character queries.
- Test all header/footer links as guest, user, and admin.
- Test mobile navigation and common-problem disclosure with keyboard and touch.
- Disable JavaScript and confirm core navigation/search still works.

## Definition of Done

Visitors can immediately understand GuideMyPC and reach a valid problem-solving path from every homepage entry point.
