# Search Conventions

GuideMyPC search combines MariaDB full-text relevance with deterministic title and `LIKE` matching. Exact guide titles rank first, then title prefixes, then full-text relevance. The fallback matching keeps short queries, error codes, punctuation, and sparse local seed data useful.

Published guides, approved safe-HTTPS downloads, and published canonical legacy Community posts implement the current searchable-document contract. Each result has a type, title, platform label, safe text excerpt, canonical legacy destination, created time, and optional difficulty or risk level. Public Knowledge remains active through its own published routes but is not currently part of this shared search projection.

`search_aliases` maps common variants to a canonical query. `search_related_queries` supplies short, curated recovery links. Both contain approved support terms only and are seeded without user data.

`search_suggestions.php` accepts GET only, returns an empty list below two characters, returns at most eight published guide/category suggestions, and is rate-limited to 30 requests per minute per client address. `search_event.php` accepts POST only and is rate-limited to 60 requests per minute per client address. Both endpoints force bounded JSON success/error envelopes, including method and rate-limit failures. They are narrow Search enhancements, not a full-resource API; basic GET search remains usable without JavaScript.

Search events are aggregate-only. They store a SHA-256 query hash, date, result type/state, result count, and aggregate count. Queries resembling email addresses, URLs, or seven-or-more digit identifiers are not recorded, and selection responses truthfully return `recorded: false` when privacy filtering discards an event. No raw query, account identity, cookie, or IP address is stored in MariaDB. Run this monthly to retain only 90 days by default:

```powershell
C:\xampp\php\php.exe scripts\purge-search-events.php
```

The verified-core release uses this MariaDB implementation only. The 250 ms target is a local performance budget, not evidence for an external search service or production host.

Run the focused helper and published-content checks after migrating and seeding:

```powershell
C:\xampp\php\php.exe tests\helpers_test.php
C:\xampp\php\php.exe tests\search_integration_test.php
C:\xampp\php\php.exe tests\search_endpoint_test.php
```
