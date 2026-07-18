# Release-Candidate Test Evidence

This record describes evidence for release-candidate commit `f8ce904` (`Strengthen guide admin workflow`). It is not final-release sign-off. Repeat the applicable checks against the submitted release commit and replace the candidate identifier before exporting the report.

| Date | Environment | Check | Expected Result | Actual Result | Status | Evidence |
| --- | --- | --- | --- | --- | --- | --- |
| 2026-07-17 | Windows/XAMPP, PHP 8.2.12, MariaDB 10.4.32 | `C:\xampp\php\php.exe scripts\verify.php` | Integration checks complete successfully | Search publication, knowledge publication, guide transaction/audit, and account checks passed | Pass | Console output recorded during local verification |
| 2026-07-17 | Windows/XAMPP, PHP 8.2.12 | PHP lint over tracked PHP files | Every tracked PHP file has valid syntax | All tracked PHP files passed | Pass | Console output recorded during local verification |
| 2026-07-17 | Local Apache | Guest requests to `admin_guides.php`, `add_guide.php`, and `edit_guide.php?id=1` | Unauthenticated users cannot access guide administration | Each route returned HTTP 403 | Pass | Local HTTP smoke check |
| 2026-07-17 | MariaDB transaction | `tests/guide_integration_test.php` | Guide/category validation, structured updates, and audit redaction roll back safely | Passed | Pass | Included in `scripts/verify.php` |
| 2026-07-19 | Windows/XAMPP, Chrome 150 headless, 320x800 | `node scripts/check-mobile-layout.js` for `guides.php`, a populated `guide.php`, and populated `search.php` | `documentElement.scrollWidth` does not exceed the 320px viewport | Each public route measured 320px document width | Pass | Console output recorded during local verification |
| 2026-07-19 | Windows/XAMPP, Chrome 150 headless, 320x800 | `node scripts/check-mobile-layout.js` for `login.php` and unauthenticated `admin_guides.php` | The displayed unauthenticated surfaces do not overflow horizontally | Each route measured 320px document width | Pass | Console output recorded during local verification |

## Remaining Final-Release Evidence

- Authenticated browser checks for guide create, edit, duplicate-slug feedback, delete confirmation, and audit-record inspection.
- Guest, user, and administrator capability checks for every administrative route.
- Authenticated browser, keyboard, screen-reader, 320px guide-form, and error-state checks required by task `020`.
- Fresh database migration, seed, source archive extraction, and final package review.
- Screenshot capture and redaction review against the final submitted commit.

Do not mark these items as passed until a named tester, date, expected/actual result, and non-sensitive evidence reference are recorded.
