# Search Conventions

GuideMyPC search combines MariaDB full-text relevance with deterministic title and `LIKE` matching. Exact guide titles rank first, then title prefixes, then full-text relevance. The fallback matching keeps short queries, error codes, punctuation, and sparse local seed data useful.

Published guides, official downloads, and published community posts implement the current searchable-document contract. Each result has a type, title, platform label, safe text excerpt, canonical destination, created time, and optional difficulty or risk level. Future knowledge-base, diagnostic, video, and error-code tables must expose equivalent fields before being added to the shared service.

`search_aliases` maps common variants to a canonical query. `search_related_queries` supplies short, curated recovery links. Both contain approved support terms only and are seeded without user data.

The suggestion endpoint accepts at least two characters, returns at most eight published guide/category suggestions, and is rate-limited to 30 requests per minute per client address. Browser autocomplete is progressive enhancement: basic GET search remains fully usable without JavaScript.

Search events are aggregate-only. They store a SHA-256 query hash, date, result type/state, result count, and aggregate count. Queries resembling email addresses, URLs, or seven-or-more digit identifiers are not recorded. No raw query, account identity, cookie, or IP address is stored in MariaDB. Run this monthly to retain only 90 days by default:

```powershell
C:\xampp\php\php.exe scripts\purge-search-events.php
```

Keep this MariaDB implementation while local representative queries stay within the 250 ms response-time budget. Reconsider Meilisearch only when measured content volume or sustained p95 latency exceeds that budget after indexing and query tuning.

Run the focused helper and published-content checks after migrating and seeding:

```powershell
C:\xampp\php\php.exe tests\helpers_test.php
C:\xampp\php\php.exe tests\search_integration_test.php
```
