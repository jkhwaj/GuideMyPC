# Route Contract Baseline

This baseline supports task `000` of `Tasks/project-structure-migration/`. It records the current route groups that must retain their legacy path and behavior during structural migration. It is not a specification for new product behavior; known inconsistent publication and model behavior is recorded separately for approval.

See [`route-inventory.md`](route-inventory.md) for the route-by-route method, protection, input, side-effect, caller, and test-coverage baseline.

## Compatibility Rules

- Legacy `*.php` paths remain canonical for the verified-core release.
- Existing forms, JavaScript, reset-link paths, navigation, sitemap output, and redirects must continue to resolve those paths. This path contract does not claim outbound mail delivery.
- Mutations retain their documented POST, CSRF, authorization, flash, and HTTP 303 PRG contracts unless a route-specific exception is recorded.
- JSON endpoints retain the `ok`, `data` or `error`, and `meta.request_id` contract.
- Controllers may change internally, but each route must preserve its approved status, response type, session effects, and database side effects.
- Owner-approved retirements are explicit compatibility exceptions. Retired paths must use the standard safe `404` response and must not redirect to another feature.

## Approved Release-Scope Retirements

The project owner approved the following final-release changes on 2026-07-22:

| Retired path or scope | Final contract |
| --- | --- |
| `ai.php` | The AI Assistant placeholder and its runtime slice are removed. Requests receive the standard safe `404` response. Diagnostics remains independently available. |
| `donate.php` | Donate and its runtime slice are removed. Requests receive the standard safe `404` response. Contact remains independently available. |
| Knowledge administration | No Knowledge administration route was implemented or allowlisted. It is excluded from the release; the verified public Knowledge routes remain active. |
| Reports | No Reports route was implemented or allowlisted. Dashboard projections remain active and are not a Reports feature. |
| Full-resource APIs | No resource API is claimed. The two narrow Search JSON endpoints documented below remain active. |
| Uploads and Maintenance Center | No active route is implemented or allowlisted. Historical schema and defensive private-path/dependency checks do not make either feature active. |
| Community v2 | The question/answer schema is not an active runtime model. The canonical legacy Community remains active. |
| Mail delivery and CSV export | Neither option is a verified release capability. Password-reset route security remains documented without a delivery guarantee. |

## Shared Contracts

| Contract | Current behavior to preserve |
| --- | --- |
| Bootstrap | Root routes load `config.php` before output during the transitional state. |
| Session keys | `user_id`, `full_name`, `role`, `_remember_selector`, `_csrf_token`, `_flash`, `_old_input`, `_guest_progress`, `viewed_guides`, and `_diagnostic_tokens`. The selector is non-secret device lookup state; the opaque validator remains only in an HttpOnly cookie. |
| Redirects | Shared redirects use HTTP 303. |
| JSON | Success: `ok`, `data`, `meta.request_id`; failure: `ok`, bounded `error`, `meta.request_id`. |
| Errors | `419` has a dedicated invalid-request response; browser errors must not expose internals. |
| Rate limiting | File-backed state lives in private storage. |

## Public and Static Routes

| Paths | Method and response | Migration contract |
| --- | --- | --- |
| `index.php` | GET HTML | Homepage aggregator for categories, guides, downloads, and Community. Migrate last as a named read model. |
| `about.php`, `contact.php`, `privacy.php`, `terms.php`, `disclaimer.php` | GET HTML | Static and legal pages rendered through the shared view boundary. |
| `sitemap.php` | GET XML | Preserve content type, public URL filtering, and legacy paths. |

## Knowledge, Guides, Downloads, Search, and Diagnostics

| Paths | Method and response | Migration contract |
| --- | --- | --- |
| `guides.php` | GET HTML | Published guide/category listing with filtering. |
| `guide.php` | GET HTML | Guide detail plus view counting, activity, guest-progress merging, and guide action forms. Extract reads before commands. |
| `knowledge.php`, `knowledge_article.php`, `glossary.php`, `error-code.php` | GET HTML | Knowledge content routes. Migrate as the first database-backed read slice. |
| `downloads.php` | GET HTML | Public records require `is_published`, `review_state = 'approved'`, and a safe HTTPS URL. Preserve legacy path while centralizing this policy in task `006`. |
| `search.php` | GET HTML | Search results and aggregate search event recording. |
| `search_suggestions.php` | GET JSON | File-rate-limited suggestions endpoint. It rejects other methods and forces bounded JSON success and error envelopes. |
| `search_event.php` | POST JSON | Search-selection telemetry. It forces bounded JSON envelopes, reports whether privacy filtering actually recorded the event, and is a documented CSRF exception that must not be broken by broad middleware. |
| `diagnostic.php` | GET HTML and redirect | May create a persistent diagnostic session and redirect. Both navigation implementations link to the seeded public flow. |
| `diagnostic_action.php` | POST HTML/redirect | CSRF-protected diagnostic state transition. Reaching an outcome sets `completed_at`; back and restart clear completion as state is recomputed. |

## Accounts and Personalization

| Paths | Method and response | Migration contract |
| --- | --- | --- |
| `login.php`, `register.php`, `forgot_password.php`, `reset_password.php` | GET/POST HTML | Preserve authentication, validation, reset-link paths, and session behavior. Login offers an explicit optional remembered-browser choice; raw persistent tokens are never stored in MariaDB. Native mail delivery is not a verified contract. |
| `settings.php` | GET/POST HTML | Authenticated settings workflow. A password change revokes remembered browsers. |
| `profile.php`, `devices.php` | GET HTML | Authenticated profile and owner-scoped remembered-browser inventory. The inventory displays only safe metadata, never selectors, validators, hashes, or raw cookies. |
| `dashboard.php` | GET HTML or redirect | Authenticated role-aware dashboard. Guests receive the standard HTTP 303 login redirect. Active users receive four personal progress/favorite/rating summaries plus activity; editors and administrators receive six operational KPIs, two bounded charts, and public recent-content projections; only administrators receive user identities and audit details. Account role/status is refreshed before projection selection; unavailable, disabled, or invalid accounts are signed out and redirected to login without receiving a projection. Dashboard is not Reports. |
| `logout.php`, `logout_all.php`, `revoke_device.php`, `account_request.php` | POST redirect | Preserve CSRF, authentication, PRG, and flash behavior. `logout.php` revokes the current remembered browser; `revoke_device.php` is owner-scoped; `logout_all.php` revokes every remembered browser and ends the current session. |
| `save_progress.php` | POST HTML redirect or JSON | Preserve both response modes and ownership checks. |
| `toggle_favorite.php`, `rate_guide.php` | POST redirect | Preserve CSRF, authorization, redirects, and guide ownership/state rules. |

## Community

| Paths | Method and response | Migration contract |
| --- | --- | --- |
| `community.php` | GET HTML and POST HTML/redirect | Active legacy post/comment workflow. Public listing and comment targets require `is_published = 1`. |
| `toggle_like.php` | POST redirect | Authenticated Community like action. |

The active routes use `community_posts`, `community_comments`, and `community_likes`. This legacy model is canonical for the migration. The newer question/answer tables remain deferred and are not active runtime routes.

## Administration

| Paths | Method and response | Migration contract |
| --- | --- | --- |
| `admin_categories.php` | GET HTML | Editor/administrator category listing with bounded search, publication filter, allowlisted sorting, and pagination. Delete controls are administrator-only. |
| `add_category.php`, `edit_category.php` | GET/POST HTML | Editor/administrator category workflows. Preserve validation, CSRF, publication capability, audit, and HTTP 303 PRG behavior. Unpublishing a category hides its category-scoped public content. |
| `admin_guides.php` | GET HTML | Editor/administrator guide listing with bounded search, publication/category filters, allowlisted sorting, and pagination. Delete controls are administrator-only. |
| `add_guide.php`, `edit_guide.php` | GET/POST HTML | Editor/administrator Guide workflows. Draft/publication, curation, official sources, structured steps, CSRF, audit, and HTTP 303 PRG are required. |
| `admin.php`, `admin_downloads.php`, `admin_users.php`, `admin_community.php`, `admin_comments.php` | GET HTML | Administrator listings/dashboard. Queries must complete before output. |
| `add_download.php`, `edit_download.php`, `edit_user.php` | GET/POST HTML | Administrator editor workflows until their feature-specific capability contracts are approved. Preserve validation, CSRF, authorization, and PRG. |
| `delete_category.php` | POST redirect | Administrator-only hard deletion. Block deletion when any known feature references the category; preserve CSRF, flash, audit, and HTTP 303 PRG behavior. |
| `delete_guide.php` | POST redirect | Administrator-only hard deletion. Block deletion when durable user or knowledge dependencies exist; preserve CSRF, flash, audit, and HTTP 303 PRG behavior. |
| `delete_comment.php`, `delete_download.php`, `delete_post.php`, `delete_user.php` | POST redirect | Administrator deletion actions. Preserve authorization, CSRF, flash, redirect, and audit behavior. |

Administration moves into the feature that owns its data. The shared audit service is a dependency, not an excuse for a separate generic admin repository.

Guide step identity is persistent across edits. `edit_guide.php` must update and reorder submitted steps by their existing IDs, insert only new steps, and delete only intentionally omitted steps. Metadata-only edits, text corrections, additions, and reordering preserve progress on retained IDs. Removing a step deletes progress for that step only; cross-guide or duplicate IDs reject the complete transaction.

## Known Policy Differences

| Area | Current difference | Required decision |
| --- | --- | --- |
| Downloads | `downloads.php` can show records regardless of publication/review state; Home and Search check `is_published`; the unused helper also checks URL safety and approval. | Approved: public records require publication, approval, and a safe HTTPS URL. Task `006` must centralize the rule before task `007` migrates aggregators. |
| Community | Community list and Home previously showed posts without the publication filter used by Search. | Resolved: public legacy post projections require `is_published = 1`; unpublished posts cannot receive public comments or likes. |
| Diagnostics | Non-guide resource types currently resolve to `community.php`. | Keep active behavior through migration; schedule typed resource resolution separately. |
| Admin audit | Structured guide operations are audited, but other administrative mutations are not consistently audited. | Apply audit consistently during task `006`. |

## Required Characterization Evidence

Before a route is moved, record or automate:

- success, validation, wrong-method, unauthenticated, unauthorized, invalid-CSRF, rate-limit, absent-resource, and unexpected-error outcomes that apply to it;
- input names, response content type, status code, redirect target, and flash/session effects;
- database reads/writes and duplicate-side-effect prevention;
- callers in forms, JavaScript, navigation, email generation, robots, and sitemap output;
- guest, user, administrator, and cross-user behavior where relevant.

No fixtures or evidence may include real credentials, tokens, cookies, private uploads, or personal data.
