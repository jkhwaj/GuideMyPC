# GuideMyPC Design System

The local design system uses CSS custom properties from `css/design-system.css`. It intentionally uses system fonts and existing locally rendered icon text, so it does not request third-party fonts or icon assets.

## Tokens

- Color: `--canvas`, `--surface`, `--text`, `--text-muted`, `--primary`, `--accent`, `--success`, and `--danger` have light and dark values.
- Layout: `--content-width` caps readable desktop content; mobile layouts use a 1rem or smaller side gutter.
- Shape and elevation: `--radius-sm`, `--radius-md`, `--radius-lg`, `--shadow-sm`, and `--shadow-md` are the shared component values.
- Motion: interaction transitions are brief and disabled for `prefers-reduced-motion`.

## Reusable Patterns

- Use `primary-btn` for the main action and `secondary-btn` for a neutral alternative.
- Use `card`, `meta-card`, `step-card`, `profile-card`, and `dashboard-panel` for surfaced content. Do not add page-specific dark backgrounds.
- Use semantic labels for controls. `sr-only` provides an accessible label when visible copy is unnecessary.
- Server flash messages are status announcements. Form failures must remain visible without JavaScript.

## Themes and Assets

Theme defaults to the system preference. The visible header control persists an explicit selection in local storage under `guidemypc-theme`. Static assets use `APP_ASSET_VERSION` from `.env`; increment it only when an asset changes in deployment.
