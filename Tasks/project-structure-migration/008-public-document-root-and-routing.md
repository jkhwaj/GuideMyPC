# Task: Public Document Root and Routing

- Status: In progress
- Priority: Critical
- Release: M5
- Dependencies: `007-search-home-sitemap-and-cross-feature-reads.md`; approved Composer deployment and proxy decisions from task `000`

## Objective

Expose only `public/` through the web server and route every approved legacy path through a centralized entry point without changing public URL or form contracts.

## Current State

The repository root is the documented XAMPP document root. Protection depends on root `.htaccess` rules and rewrite availability. Application source, database migrations, scripts, tests, tasks, documentation, and configuration share the same filesystem tree as public routes and assets.

## Scope

- Add `public/index.php` as the web front controller.
- Add route definitions for public, administrator, and progressive-enhancement endpoints.
- Map existing `*.php` paths and methods to named routes.
- Move CSS, JavaScript, images, robots, and other public assets under `public/`.
- Add Apache rewrite rules and documented XAMPP virtual-host or Alias configurations.
- Document equivalent production Nginx/front-controller behavior.
- Generate URLs and canonical metadata from route/request context rather than physical script paths.
- Verify hostname-root and `/GuideMyPC/` subdirectory installations.
- Keep private runtime storage external and deny all repository source by construction.

## Non-Goals

- Replacing legacy paths with clean URLs
- Redirecting `*.php` URLs
- Changing form actions or JavaScript endpoint names
- Adding a separate API application
- Moving private storage into a repository `storage/` directory
- Enforcing unrelated CSP or caching policy changes

## Implementation Steps

1. Define named routes from the approved route inventory, including method and response type.
2. Add the front controller and make web bootstrap execute exactly once.
3. Add deterministic handling for unknown routes, wrong methods, and route-specific security requirements.
4. Move public assets and update centralized URL generation.
5. Route legacy filenames through named routes without redirects.
6. Update password-reset links, JavaScript requests, forms, navigation, sitemap, robots, and canonical metadata.
7. Add an isolated XAMPP vhost or Alias pointing to `public/` and test before changing the default setup.
8. Add production Apache and Nginx examples with required permissions and fallback behavior.
9. Add automated private-path denial and legacy-route smoke tests.
10. Update local setup, deployment, and troubleshooting documentation.

## Database Changes

None.

## Security and Privacy

- `.env`, `app/`, `bootstrap/`, `config/`, `database/`, `scripts/`, `tests/`, `Tasks/`, documentation, Composer metadata where unnecessary, and private runtime files must not be web-addressable.
- Route matching must enforce exact method, authentication, authorization, CSRF, and rate-limit policies.
- Path traversal, encoded path, dotfile, and direct PHP source requests must fail safely.
- Trusted proxy handling must use the approved allowlist before deriving HTTPS or client IP.
- Uploads remain private and are served only through authorized application endpoints where applicable.

## Accessibility

Routing errors must render the same accessible 404, 403, 419, 429, and 500 responses. Asset moves must not cause unstyled or script-dependent inaccessible states.

## Affected Files

- `public/index.php`
- `public/.htaccess`
- `public/assets/`
- `routes/web.php`
- `routes/admin.php`
- `routes/api.php`
- root route compatibility files
- current root `.htaccess`
- CSS, JavaScript, image, robots, and sitemap references
- URL generation and route metadata code
- Apache/Nginx setup and deployment documentation
- HTTP route and private-path tests

## Rollback Strategy

Keep the original repository-root local configuration available until the isolated public-root smoke matrix passes. Roll back by restoring the previous Apache mapping, not by copying private source into `public/`. Preserve the route table and asset work for continued correction.

## Acceptance Criteria

- [ ] Apache and production examples expose only `public/`.
- [ ] Every approved legacy path resolves through a named route with its documented methods and response type.
- [ ] No compatibility redirect or clean URL change is introduced.
- [ ] CSS, JavaScript, images, suggestions, telemetry, JSON actions, reset links, sitemap, robots, and canonical URLs work under both supported base-path configurations.
- [ ] Unknown routes and wrong methods return the approved safe responses.
- [ ] Private source, configuration, SQL, scripts, tests, tasks, logs, sessions, caches, uploads, and rate-limit files are unreachable.
- [ ] Web bootstrap, security headers, and session setup execute exactly once per request.
- [ ] Local and production configuration instructions include rollback steps.

## Validation

- Run the complete route matrix through Apache rather than PHP include tests alone.
- Test guest, user, administrator, HTML, JSON, XML, redirect, wrong-method, CSRF, rate-limit, and not-found behavior.
- Probe every private directory and sensitive extension with rewrite enabled and disabled where applicable.
- Test hostname-root and `/GuideMyPC/` Alias configurations, HTTP and HTTPS, and the approved proxy setup.
- Crawl links, assets, forms, JavaScript endpoints, canonical tags, robots, and sitemap URLs.
- Extract the deployment package into a clean location and start it using only documented steps.

## Definition of Done

All existing application behavior is served through named routes from a public-only document root, legacy URLs remain compatible, and no repository or private runtime material is directly web-accessible.

## Implementation Evidence

- Audited the current root document root, root `.htaccess`, legacy route contracts, and CSS/JavaScript locations. The root `.htaccess` provides only temporary rewrite-dependent protection and cannot meet the public-only boundary.
- Production Composer delivery is approved as a deploy artifact containing locked production dependencies; source archives remain clean.
- The initial proxy policy is approved as direct Apache HTTPS only. Forwarded headers remain untrusted until a separately approved allowlist exists.
- Live front-controller activation is deferred until named legacy route dispatch and public asset migration can land together; activating `public/` before then would break legacy scripts, metadata derived from script paths, or CSS/JavaScript URLs.
