# Screenshot Manifest

The ten ignored PNG files below are the reviewed evidence embedded in
`GuideMyPC-Final-Report.docx` (images 5 through 14) and copied into the strict
source package. They were captured on 2026-07-29 from the reviewed local
release tree with seeded content and sanitized test accounts. The evidence
contains no passwords, cookies, tokens, credentials, real email addresses,
personal information, bookmarks, or private local paths.

| File | Route | User role | Viewport | Caption | Alt text | Capture date | Redaction notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `01-homepage.png` | `index.php` | Guest | 1440x900 | Home categories use emoji icons | GuideMyPC homepage with Android, iPhone/iPad, Linux, macOS, Wi-Fi, and Windows category cards | 2026-07-29 | Public seeded content only; no account or browser data |
| `02-downloads-public.png` | `downloads.php` | Guest | 1425x1937 full-page desktop | Approved official Download catalog without duplicates | Public Download cards for approved official resources including Rufus, CPU-Z, HWMonitor, Windows 11, NVIDIA, and Samsung Magician | 2026-07-29 | Public seeded catalog only; external destinations are not opened |
| `03-admin-downloads.png` | `admin_downloads.php` | Sanitized administrator test account | 1425x1730 full-page desktop | Admin Downloads sorted by descending ID | Manage Downloads table showing approved, published resources and permitted actions | 2026-07-29 | Sanitized role label only; no email, token, or credential is shown |
| `04-login-remember-me.png` | `login.php` | Guest | 1440x900 | Login offers explicit Remember me | Empty login form with the optional Keep me signed in for up to 30 days control | 2026-07-29 | Empty fields; no email address, password, or remembered cookie is shown |
| `05-login-mobile-320px.png` | `login.php` | Guest | 320x800 | Responsive login at 320px with a fully visible Remember me option | Mobile login form with visible navigation, an intact login card, and the complete Remember me text | 2026-07-29 | Empty fields; no email address, password, cookie, token, or browser data is shown. Captured at deviceScaleFactor 1; `scrollWidth === clientWidth` (no horizontal overflow). |
| `06-user-dashboard.png` | `dashboard.php` | Sanitized regular test account | 1440x900 | Personal user dashboard | My dashboard with personal guide, favorite, rating, and recent-activity projections | 2026-07-29 | Sanitized role label and zero-value seeded projections only; no personal profile details |
| `07-admin-guides.png` | `admin_guides.php` | Sanitized administrator test account | 1425x1781 full-page desktop | Responsive Guide administration | Manage Guides filters and a published Guide table with permitted edit and delete actions | 2026-07-29 | Sanitized role label and seeded Guide content only |
| `08-diagnostics.png` | `diagnostic.php?flow=pc-no-power` | Guest | 1440x900 | Guided diagnostic flow | PC power diagnostic question with answer choices, Continue, Back, and Restart controls | 2026-07-29 | Guest flow only; no session identifier or account data is shown |
| `09-safe-404.png` | `missing-page.php` | Guest | 1440x900 | Safe public 404 response | Page not found response with a return-home action and no implementation details | 2026-07-29 | No directory listing, request identifier, server path, or private detail is shown |
| `10-mobile-320px.png` | `downloads.php` | Guest | 320x800 | Responsive public Downloads at 320px with a readable header and Download card | Mobile Downloads layout with Menu and Login controls, a readable header, and a complete approved Download card | 2026-07-29 | Public seeded catalog only; no account or browser data. Captured at deviceScaleFactor 1; `scrollWidth === clientWidth` (no horizontal overflow). |
