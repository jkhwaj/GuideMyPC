# Task: Responsive Design System and Layout

- Status: Not started
- Priority: High
- Release: R1
- Dependencies: `004-application-structure-and-error-handling.md`

## Objective

Create a distinctive, trustworthy, accessible visual foundation that works across desktop and mobile and can be reused by every MVP feature.

## Current State

The prototype has one custom stylesheet, shared header/navbar/footer fragments, desktop-oriented navigation, and a cache-busting timestamp that prevents effective browser caching. Metadata and page-title handling are minimal.

## Scope

- Define color, type, spacing, radius, elevation, layout, icon, and motion tokens.
- Build reusable buttons, links, forms, alerts, badges, cards, metadata rows, breadcrumbs, pagination, dialogs, empty states, and skeleton/loading states.
- Implement responsive header, mobile navigation, footer, content widths, and grid patterns.
- Add light/dark theme support with a visible toggle and system-preference default.
- Establish visible keyboard focus, reduced-motion behavior, contrast requirements, and touch-target sizes.
- Replace timestamp cache busting with a stable asset version strategy.
- Support per-page titles, descriptions, canonical URLs, and social metadata.

## Non-Goals

- A separate component framework
- React conversion
- Final page-specific content
- Decorative animation that delays troubleshooting

## Implementation Steps

1. Audit existing CSS and identify reusable patterns versus obsolete selectors.
2. Define CSS custom properties and responsive breakpoints.
3. Refactor shared layout fragments and mobile navigation.
4. Add reusable form and status-message patterns.
5. Implement dark mode with persistent local preference and no flash where practical.
6. Add an asset-version constant or build-free version mechanism.
7. Document examples in a local-only style reference or representative pages.

## Database Changes

None. Theme preference may remain client-side for MVP; account synchronization can be considered later.

## Security and Privacy

Avoid third-party font or icon requests unless privacy, licensing, integrity, and performance are reviewed. Do not render untrusted HTML in reusable content components.

## Accessibility

- Target WCAG 2.2 AA.
- Support 320px viewport width and 200% zoom without content loss.
- Ensure logical focus order, skip navigation, landmarks, labels, error association, and minimum target sizes.
- Respect `prefers-reduced-motion` and `prefers-color-scheme`.

## Affected Files

- `css/style.css`
- `js/script.js`
- `includes/header.php`
- `includes/navbar.php`
- `includes/footer.php`
- reusable presentation includes added during implementation

## Acceptance Criteria

- [ ] Shared layout works at 320px, tablet, laptop, and wide desktop sizes.
- [ ] Navigation is operable with keyboard, touch, and screen reader.
- [ ] Light and dark modes meet contrast targets and persist appropriately.
- [ ] Common controls have consistent hover, focus, disabled, loading, and error states.
- [ ] CSS uses stable cache versioning rather than the current timestamp.
- [ ] Pages can set unique title and metadata values.

## Validation

- Test representative pages at 320px, 768px, 1280px, and 200% zoom.
- Run keyboard-only and automated accessibility checks.
- Inspect caching behavior and confirm unchanged CSS can be cached.
- Check light/dark mode in supported browsers.

## Definition of Done

Subsequent feature pages can be assembled from consistent, responsive, accessible patterns without creating one-off layout systems.
