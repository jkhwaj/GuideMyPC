# Screenshot Manifest

The ten screenshots below were captured on 2026-07-23 from the reviewed
release-candidate tree using a disposable seeded `guidemypc_screenshot_test`
database and a sanitized `Submission Reviewer` account. The strict package
manifest binds their hashes to the final release commit. Canonical root legacy
`*.php` URLs were used; the private image files remain ignored in Git and are
overlaid only by the package builder.

The visual narrative covers the retained verified core: Home, Guides, public
Knowledge, approved Downloads/Search where useful, accounts/progress,
Diagnostics, role-aware Dashboard KPI/charts, canonical legacy Community, and
documented administration. Do not add screenshots for AI Assistant, Uploads,
Maintenance Center, Knowledge administration, product Reports, full-resource
APIs, Donate, Community v2, mail, CSV, clean URLs, or production hosting.

| File | Route | Role | Viewport | Commit | Caption | Alt Text | Capture Date | Redaction Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `01-home-desktop.png` | `index.php` | Guest | 1440x900 | Bound by package manifest | Verified-core entry point | Homepage navigation and bounded support search | 2026-07-23 | Reviewed seeded public content only; no account data |
| `02-guides-desktop.png` | `guides.php` | Guest | 1440x900 | Bound by package manifest | Published Guide library | Filtered published Guides and category navigation | 2026-07-23 | Reviewed seeded public content only |
| `03-guide-mobile.png` | `guide.php?slug=check-windows-update-issue` | Guest | 320x800 | Bound by package manifest | Responsive structured Guide | Narrow view of safe steps and Guide metadata | 2026-07-23 | No account, session, or local environment data |
| `04-knowledge-desktop.png` | `knowledge.php` | Guest | 1440x900 | Bound by package manifest | Public Knowledge library | Published Knowledge articles, types, and categories | 2026-07-23 | Reviewed seeded public content only |
| `05-search-desktop.png` | `search.php?q=windows&type=guide` | Guest | 1440x900 | Bound by package manifest | Bounded public Search | Published Guide results and retained filters | 2026-07-23 | Public query and sample content only |
| `06-diagnostic-desktop.png` | `diagnostic.php?flow=pc-no-power` | Guest | 1440x900 | Bound by package manifest | Guided Diagnostic step | Diagnostic question, answer options, back, and restart controls | 2026-07-23 | Browser chrome omitted; no session identifier visible |
| `07-community-authenticated.png` | `community.php` | Sanitized authenticated test user | 1440x900 | Bound by package manifest | Canonical Community workflow | Legacy Community posting interface for a sanitized authenticated user | 2026-07-23 | Disposable `Submission Reviewer`; no Community v2 data |
| `08-dashboard-admin.png` | `dashboard.php` | Sanitized administrator | 1440x900 | Bound by package manifest | Role-aware Dashboard | Six operational KPIs and two charts for a bounded administrator projection | 2026-07-23 | Disposable account; explicitly not product Reports |
| `09-guide-admin.png` | `admin_guides.php` | Sanitized administrator | 1440x900 | Bound by package manifest | Verified Guide administration | Bounded Guide administration listing and permitted controls | 2026-07-23 | Disposable account and seeded test content only |
| `10-safe-404-mobile.png` | `missing-page.php` | Guest | 320x800 | Bound by package manifest | Responsive safe error state | Narrow standard 404 page without internal details | 2026-07-23 | Cookies cleared; no request ID, local path, or account data |
