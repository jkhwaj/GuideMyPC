# Third-Party Inventory

GuideMyPC has no third-party Composer packages and no npm manifest. The exact
versions below were reconfirmed during the 2026-07-22 to 2026-07-23 readiness
work. They are local release facts, not production-hosting evidence.

## Third-Party Runtime and Shipped Assets

| Name and Version | Category | Purpose | Canonical Source | License | Delivery Method |
| --- | --- | --- | --- | --- | --- |
| PHP 8.2.12 | Runtime | Server-side application runtime | https://www.php.net/ | PHP License | XAMPP/local host |
| Apache HTTP Server 2.4.58 | Runtime | Local web server | https://httpd.apache.org/ | Apache-2.0 | XAMPP/local host |
| MariaDB 10.4.32 | Runtime | Relational database | https://mariadb.org/ | GPL-2.0 | XAMPP/local host |
| Chart.js 4.5.0 | Application asset | Progressive Dashboard charts with accompanying data tables | https://www.chartjs.org/ | MIT | Pinned local `public/assets/js/chart.umd.min.js`; license in `public/assets/js/chartjs-LICENSE.md` |
| YouTube Privacy-Enhanced Mode (service version not applicable) | Optional browser service present in source | Approved privacy-enhanced video embedding where configured; not a separate final-scope feature claim | https://support.google.com/youtube/answer/171780 | YouTube Terms of Service | `youtube-nocookie.com` iframe only for an approved URL |

## Development and Verification Tools

| Name and Version | Purpose | Source / Terms | Evidence status |
| --- | --- | --- | --- |
| Git 2.55.0.windows.2 | Version control and commit-based packaging | https://git-scm.com/ / GPL-2.0-only | Exact readiness-baseline version |
| Composer 2.10.2 | Manifest validation and PSR-4 autoload generation; no third-party package install in the current lock | https://getcomposer.org/ / MIT | Exact readiness-baseline version |
| Google Chrome 150.0.7871.130 | Local browser and headless viewport checks | https://www.google.com/chrome/ / Google Chrome Terms of Service | Exact readiness-baseline version |
| Windows 10 Pro 25H2, build 26200 | Local operating system | https://www.microsoft.com/windows / Microsoft Software License Terms | Reconfirmed 2026-07-23; component-level XAMPP versions are recorded above |
| Windows PowerShell 5.1.26100.8875 | Release and package automation | https://learn.microsoft.com/powershell/ / Microsoft terms | Reconfirmed 2026-07-23 |
| Node.js 24.18.0 | Temporary Chrome DevTools screenshot automation | https://nodejs.org/ / MIT | Used only for local capture tooling; no npm package or Node runtime ships |

## Submission Artifact Tools

| Tool | Purpose | Source / Terms | Evidence status |
| --- | --- | --- | --- |
| Microsoft Word 16.0, build 16.0.20131 | Create, reopen, paginate, and render the private `Readme.docx` and Hebrew final report | https://www.microsoft.com/microsoft-365/word / Microsoft terms | Both ignored DOCX files opened successfully; report rendered to a 28-page review PDF |
| Visual Paradigm Community Edition 18.1, official build 20260628 | Create native `GuideMyPC.vpp` with four diagrams | https://www.visual-paradigm.com/ / Community Edition non-commercial terms | VPP opened with Use Case, Class, Activity, and State Machine diagrams; four PNG exports inspected |

## First-Party Assets

`public/assets/css/style.css`, `public/assets/css/design-system.css`,
`public/assets/js/script.js`, and `public/assets/js/guide-editor.js` are
GuideMyPC project source, not third-party libraries. No third-party font, icon,
or CSS library is currently inventoried. Final package validation must preserve
this distinction.
