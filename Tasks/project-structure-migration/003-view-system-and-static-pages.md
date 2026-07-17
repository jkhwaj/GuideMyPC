# Task: View System and Static Pages

- Status: In progress
- Priority: High
- Release: M2
- Dependencies: `002-core-security-and-compatibility-layer.md`

## Objective

Establish a plain PHP rendering boundary with explicit page metadata and prove it by migrating static and legal pages before database-backed features.

## Current State

Root route scripts set loose page variables and include shared header, navbar, and footer files. Those partials read session state and server variables directly. Page metadata derives in part from `SCRIPT_NAME`, which will not identify the logical route after a front controller is introduced.

## Scope

- Add a small plain PHP view renderer under `Core`.
- Move layouts, navigation, footer, flash messages, and error presentation into `resources/views/`.
- Define explicit view data and page metadata contracts.
- Migrate About, Contact, Privacy, Terms, Disclaimer, Donate, and other truly static pages.
- Keep existing `*.php` route scripts as thin compatibility dispatchers.
- Preserve asset paths, canonical URLs, navigation state, titles, descriptions, and flash behavior.
- Ensure authorization, redirects, and data loading finish before output begins.

## Non-Goals

- Adding Twig, Blade, or another template engine
- Migrating Home or database-backed features
- Redesigning the visual system
- Changing content, canonical paths, or navigation labels
- Requiring JavaScript for page rendering
- Introducing clean URLs

## Implementation Steps

1. Define the renderer API, layout selection, escaping expectations, and explicit data scope.
2. Create layout, partial, error, and feature view directories.
3. Replace `SCRIPT_NAME` metadata inference with named route/page metadata.
4. Pass navigation user state explicitly rather than reading it in templates.
5. Move shared header, navbar, footer, and flash presentation into views.
6. Migrate one static page and compare output before migrating the remaining static/legal pages.
7. Reduce each migrated root script to a compatibility dispatcher.
8. Add rendering and HTTP characterization tests for desktop and mobile output.
9. Document escaping, partial, layout, and asset conventions.

## Database Changes

None. Migrated static routes must not request a database connection.

## Security and Privacy

- Escape untrusted output by default and document the narrow handling of trusted HTML.
- Views must not perform authorization, redirects, SQL, filesystem writes, or logging.
- Error views must not expose stack traces, paths, SQL, configuration, or private data.
- Canonical and asset URLs must use centralized URL generation rather than raw host headers.

## Accessibility

- Preserve semantic landmarks, skip links, heading hierarchy, focus visibility, navigation labels, and accessible flash/error messages.
- Validate all migrated pages at desktop and 320px mobile width.
- Keep core content and navigation usable without JavaScript.

## Affected Files

- view renderer under `app/Core/`
- static page controllers under `app/Features/Pages/`
- `resources/views/layouts/`
- `resources/views/partials/`
- `resources/views/pages/`
- `resources/views/errors/`
- current shared layout files under `includes/`
- migrated root static routes
- view and HTTP tests
- `docs/project-structure.md`
- `docs/application-conventions.md`

## Rollback Strategy

Migrate one page at a time. Each compatibility route can temporarily return to the existing include-based layout while the new renderer remains available to completed pages.

## Acceptance Criteria

- [ ] The renderer uses plain PHP and exposes only explicit view data.
- [ ] No migrated view accesses `$conn`, performs SQL, redirects, or changes session state.
- [ ] Page metadata identifies the logical route without depending on front-controller `SCRIPT_NAME`.
- [ ] Static/legal pages render without MariaDB.
- [ ] Legacy paths, titles, descriptions, canonical URLs, assets, and navigation remain compatible.
- [ ] Shared flash and error messages remain accessible and escaped.
- [ ] All processing that can redirect or change status occurs before rendering.
- [ ] Old layout includes remain only as compatibility delegates or are unused by migrated routes.
- [ ] View conventions are documented for downstream feature tasks.

## Validation

- Compare normalized HTML, status, title, description, canonical URL, asset URLs, and navigation for each migrated route.
- Run routes with MariaDB stopped.
- Test normal, flash-message, 404, 403, 419, and 500 rendering.
- Verify keyboard navigation, focus visibility, heading structure, landmarks, and 320px behavior.
- Disable JavaScript and confirm all migrated pages remain usable.
- Run PHP lint and the existing automated suite.

## Definition of Done

Static pages and shared presentation use the documented rendering boundary with no database dependency, and database-backed features can adopt the same layout without relying on procedural include globals.

## Implementation Evidence

- Added `GuideMyPC\Core\View` with explicit view data and a plain PHP layout.
- Added Pages controller and view templates for the About page only; `about.php` remains the legacy compatibility entry point.
- Layout metadata, navigation state, and flash messages are passed as explicit data rather than inferred from `SCRIPT_NAME` or read from session state by templates.
- Existing `includes/` layout templates and all other routes remain unchanged while About-page parity is characterized.
- PHP lint passed for the renderer, Pages controller, About entry point, and new templates; `tests/helpers_test.php` passes and asserts About metadata and body output.
- Direct CLI rendering of `about.php` produces the expected title, canonical URL, assets, navigation landmarks, and About heading without opening MariaDB.
