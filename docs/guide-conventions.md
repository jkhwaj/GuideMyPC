# Structured Repair Guides

Guide content is plain text. The guide page escapes every editorial field before rendering, including instructions, expected results, warnings, recovery paths, tool names, and source titles. Do not add arbitrary HTML, executable code, or copied third-party media.

Each guide needs a platform/version, difficulty, estimated time, risk level, prerequisites, backup warning, review date, required tools, and next actions. Each step needs an action and should include a title, expected result, warning when risk exists, and recovery path when a safe alternative exists.

Image URLs are limited to HTTPS on approved official/support image hosts. Video URLs are limited to valid YouTube IDs. The page does not create a third-party frame until a visitor explicitly chooses to load the privacy-enhanced `youtube-nocookie.com` embed; the written steps remain the accessible fallback.

Guide edits update metadata, structured steps, and tool records in one database transaction. A failed step insertion rolls the complete edit back. `guide_sources` should use official HTTPS sources only. Print mode hides navigation while retaining every step, warning, and source URL.

Guests can complete steps during the current browser session. When they sign in on the same guide, valid session progress is merged into the persistent account checklist. Do not promise that guest progress survives a new session or another device.

After migrating and seeding, run the focused checks:

```powershell
C:\xampp\php\php.exe tests\helpers_test.php
C:\xampp\php\php.exe tests\guide_integration_test.php
```
