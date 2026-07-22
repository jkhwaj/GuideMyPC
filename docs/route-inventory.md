# Route Inventory

This is the route-by-route companion to [`route-contracts.md`](route-contracts.md). It records the current legacy contract that must be preserved while `public/index.php` dispatches each approved path. It is a characterization baseline, not a specification for unapproved product changes.

`GET~` means the route is intended to be GET but does not yet reject another method explicitly. `CSRF` applies to the documented POST path. All redirects use HTTP 303 through the shared helper.

## Public Content

| Path | Method / response | Protection and inputs | Side effects and coverage |
| --- | --- | --- | --- |
| `index.php` | GET~ HTML | Public; home search form submits `q`. | Reads categories, guides, downloads, Community posts/users. Called by navigation and sitemap; no direct route test. |
| `about.php` | GET~ HTML | Public; no inputs. | Called by footer. View-renderer coverage. |
| `ai.php` | GET~ HTML | Public; no inputs. | Links to a diagnostic flow; no direct test. |
| `contact.php` | GET~ HTML | Public; no inputs. | Called by footer and legal pages; no direct test. |
| `disclaimer.php` | GET~ HTML | Public; no inputs. | Called by footer; no direct test. |
| `donate.php` | GET~ HTML | Public; no inputs. | Called by footer; no direct test. |
| `privacy.php` | GET~ HTML | Public; no inputs. | Called by footer; no direct test. |
| `terms.php` | GET~ HTML | Public; no inputs. | Called by footer; no direct test. |
| `guides.php` | GET HTML | Public; `category`, `search`, `page`. | Reads published categories, guides, search projection, and ratings. Called by nav, home, and Search; covered by guide-library integration tests. |
| `guide.php` | GET HTML | Public; `slug`. | Reads guide content, steps, tools, sources, relations, ratings, and progress; increments views once/session, may write activity and merge guest progress. Called by Guides, Home, Knowledge, Sitemap, and Diagnostics; covered indirectly by guide tests. |
| `knowledge.php` | GET~ HTML | Public; `type`, `category`. | Reads published knowledge articles/categories. Called by nav/footer; covered indirectly by knowledge tests. |
| `knowledge_article.php` | GET~ HTML | Public; `slug`. | Reads published article, category, sources, relations, and guide references. Called by Knowledge, Glossary, Error Code, Guides, and Sitemap; covered indirectly by knowledge tests. |
| `glossary.php` | GET~ HTML | Public; no inputs. | Reads published glossary articles/categories. Called by Knowledge; no direct test. |
| `error-code.php` | GET~ HTML | Public; `code`. | Reads published error-code articles. Called by Knowledge; no direct test. |
| `downloads.php` | GET~ HTML | Public; no inputs. | Reads only published, approved, safe-HTTPS downloads. Called by nav, Home, and admin; policy has helper coverage. |
| `sitemap.php` | GET~ XML | Public; no inputs. | Emits legacy canonical URLs for published guides/articles/categories. Consumed by crawlers; no direct test. |

## Search and Diagnostics

| Path | Method / response | Protection and inputs | Side effects and coverage |
| --- | --- | --- | --- |
| `search.php` | GET~ HTML | Public; `q`, `type`, `platform`, `difficulty`, `safety`, `recency`, `page`. | Reads search projections; writes aggregate search events for non-empty queries. Called by Home/Search forms and client JavaScript; covered by search integration tests. |
| `search_suggestions.php` | GET JSON | Public; rate limit 30/min; `q`. | Reads search projections. Called by autocomplete in `public/assets/js/script.js`; covered indirectly by Search tests. |
| `search_event.php` | POST JSON | Public; **CSRF exception**; rate limit 60/min; `query`, `result_type`. | Writes aggregate search events. Called by search-selection JavaScript; no direct endpoint test. |
| `diagnostic.php` | GET~ HTML/303 | Public; `flow` or `session`. | Reads and may create diagnostic flow/session/node/option/resource state. Called by AI link and diagnostic redirects; no direct test. |
| `diagnostic_action.php` | POST redirect | Session holder; CSRF; `session`, `action`, `option`. | Writes/deletes diagnostic answers and updates session; authenticated use writes user activity. Called by diagnostic forms; no direct test. |

## Accounts and Personalization

| Path | Method / response | Protection and inputs | Side effects and coverage |
| --- | --- | --- | --- |
| `login.php` | GET~/POST HTML/303 | Public; POST CSRF and rate limit 5/15 min; `email`, `password`. | Reads users; writes session, account security events, and may merge guest progress. Called by nav/Register/Forgot Password; covered indirectly by account tests. |
| `register.php` | GET~/POST HTML/303 | Public; POST CSRF and rate limit 3/hour; `full_name`, `email`, `password`. | Inserts user, writes session/security event, merges guest progress. Called by nav/Login; covered indirectly by account tests. |
| `forgot_password.php` | GET~/POST HTML | Public; POST CSRF and rate limit 3/hour; `email`. | Reads users, writes/prunes reset tokens, and sends mail when configured. Called by Login; covered indirectly by account tests. |
| `reset_password.php` | GET~/POST HTML/303 | Public token holder; POST CSRF; `token`, `password`, `password_confirmation`. | Consumes reset token, updates password, writes security event. Called by reset-email link; covered indirectly by account tests. |
| `logout.php` | POST redirect | Session optional; CSRF; no inputs. | Clears/destroys session; writes logout event when authenticated. Called by shared navigation; no direct test. |
| `profile.php` | GET~ HTML | Active authenticated user; no inputs. | Reads own account, favorites, progress, activity, and data requests with guide/category data. Called by nav; no direct test. |
| `settings.php` | GET~/POST HTML/303 | Authenticated; POST CSRF; `full_name`, `current_password`, `new_password`. | Updates own account/session; password changes write security events. Called by Profile; covered indirectly by account tests. |
| `dashboard.php` | GET HTML/303 | Authenticated; no inputs. | Refreshes account/role, signs invalid accounts out, and reads role-scoped guide/account/community/download/audit projections. Called by nav; covered by dashboard integration tests. |
| `account_request.php` | POST redirect | Authenticated; CSRF; `request_type` (`export` or `deletion`). | Inserts account data request and writes a security event. Called by Profile form; no direct test. |
| `save_progress.php` | POST HTML redirect or JSON | Guest or authenticated; CSRF; `step_id`, `completed`, `guide_slug`. | Updates guest session progress or user progress after published-step validation. Called by Guide form and fetch enhancement; covered indirectly by guide tests. |
| `toggle_favorite.php` | POST redirect | Authenticated; CSRF; `guide_id`, `slug`. | Toggles favorites after published-guide validation. Called by Guide form; no direct test. |
| `rate_guide.php` | POST redirect | Authenticated; CSRF; `guide_id`, `rating`, `slug`. | Upserts guide rating after published-guide validation. Called by Guide form; no direct test. |

## Community

| Path | Method / response | Protection and inputs | Side effects and coverage |
| --- | --- | --- | --- |
| `community.php` | GET~/POST HTML/303 | GET public; POST CSRF and login. Post: `add_post`, `title`, `content`, rate limit 5/hour. Comment: `add_comment`, `post_id`, `comment`, rate limit 15/hour. | Inserts legacy posts/comments only for published comment targets; reads posts/comments/likes/users. Called by nav, Home, and admin; policy has helper coverage. |
| `toggle_like.php` | POST redirect | Authenticated; CSRF; `post_id`. | Validates a published post, then toggles legacy likes. Called by Community form; no direct test. |

## Administration

| Path | Method / response | Protection and inputs | Side effects and coverage |
| --- | --- | --- | --- |
| `admin.php` | GET~ HTML | Administrator; no inputs. | Reads dashboard counts/recent data from users, guides, categories, downloads, Community, ratings, and favorites. Called by nav; no direct test. |
| `admin_categories.php` | GET HTML | Editor/administrator with refreshed role; `q`, `status`, `sort`, `direction`, `per_page`, `page`. | Reads categories and guide/article counts. Called by admin dashboard; category integration coverage. |
| `add_category.php` | GET/POST HTML/303 | Editor/administrator with refreshed role; POST CSRF; `name`, `slug`, `description`, `icon`, `is_published`, `featured_order`. | Inserts category and updates search projection/audit through service. Called by category admin; category integration coverage. |
| `edit_category.php` | GET/POST HTML/303 | Editor/administrator with refreshed role; POST CSRF; `id` and category fields. | Updates category, dependent publication/search state, and audit data. Called by category admin; category integration coverage. |
| `delete_category.php` | POST redirect | Administrator with refreshed role; CSRF; `id`. | Deletes category only when Guides, Knowledge, Diagnostics, Maintenance, and Community dependencies permit it. Called by category admin form; category integration coverage. |
| `admin_guides.php` | GET HTML | Editor/administrator with refreshed role; `q`, `status`, `category`, `sort`, `direction`, `per_page`, `page`. | Reads guides, categories, step counts, and progress. Called by admin dashboard; guide-admin integration coverage. |
| `add_guide.php` | GET/POST HTML/303 | Editor/administrator with refreshed role; POST CSRF; guide metadata, `steps`, `sources`. | Writes guide, steps, sources, trusted-source/search data, and audit data. Called by Guide admin; guide-admin integration coverage. |
| `edit_guide.php` | GET/POST HTML/303 | Editor/administrator with refreshed role; POST CSRF; `id`, guide metadata, persistent step IDs/`steps`, `sources`. | Updates guide/steps/sources/search/audit data and deletes progress for intentionally removed steps. Called by Guide admin; guide-admin integration coverage. |
| `delete_guide.php` | POST redirect | Administrator with refreshed role; CSRF; `id`. | Deletes only when progress, favorites, ratings, and knowledge dependencies permit it. Called by Guide admin form; guide-admin integration coverage. |
| `admin_downloads.php` | GET~ HTML | Administrator; no inputs. | Reads all downloads. Called by admin dashboard; no direct test. |
| `add_download.php` | GET~/POST HTML/303 | Administrator; POST CSRF; `name`, `description`, `official_url`, `category`, `review_state`, `is_published`. | Inserts download. Called by Download admin; helper policy coverage only. |
| `edit_download.php` | GET~/POST HTML/303 | Administrator; POST CSRF; `id` and Download fields. | Reads/updates download. Called by Download admin; helper policy coverage only. |
| `delete_download.php` | POST redirect | Administrator; CSRF; `id`. | Deletes download. Called by Download admin form; no direct test. |
| `admin_users.php` | GET~ HTML | Administrator; `success`. | Reads users. Called by admin dashboard; no direct test. |
| `edit_user.php` | GET~/POST HTML/303 | Administrator; POST CSRF; `id`, `full_name`, `email`, `role`. | Updates user, writes admin audit, and may refresh own session. Called by User admin; authorization/account indirect tests. |
| `delete_user.php` | POST redirect | Administrator; CSRF; `id`. | Deletes non-self user. Called by User admin form; no direct test. |
| `admin_community.php` | GET~ HTML | Administrator; no inputs. | Reads Community posts/users. Called by admin dashboard; no direct test. |
| `admin_comments.php` | GET~ HTML | Administrator; no inputs. | Reads Community comments/posts/users. Called by admin dashboard; no direct test. |
| `delete_post.php` | POST redirect | Administrator; CSRF; `id`. | Deletes matching Community likes/comments then post. Called by Community admin form; no direct test. |
| `delete_comment.php` | POST redirect | Administrator; CSRF; `id`. | Deletes Community comment. Called by Comment admin form; no direct test. |

## Inventory Validation

- The 55 root web entry scripts, excluding `config.php`, exactly match the union of `routes/web.php`, `routes/admin.php`, and `routes/api.php`.
- Root route files that are intended to be GET but currently lack a method guard are marked `GET~`. Their method behavior must be characterized before moving them behind centralized method handling.
- `search_event.php` is the only recorded public POST JSON CSRF exception. Do not apply broad POST middleware without preserving that exception.
- Test coverage listed here is current coverage, not a claim that every route has complete characterization tests.
