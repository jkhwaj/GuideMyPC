# Account and Activity Conventions

GuideMyPC keeps public troubleshooting content available without an account. Accounts persist favorites, guide progress, ratings, canonical Community activity, and recently viewed guide titles in the central MariaDB database. Guest checklist state is held only in the browser session and is merged after successful registration or login without overwriting an existing progress record.

## Remembered Browsers

Remember me is optional and starts only after a successful password login. Each browser receives an opaque selector/validator cookie with `HttpOnly`, `SameSite=Lax`, a bounded lifetime, and `Secure` in production. MariaDB stores only the selector and a SHA-256/HMAC validator hash; raw validators, cookie values, and hashes never appear in a page or device list. Automatic login rotates the validator, disabled/deleted accounts cannot be restored, and the account owner can revoke one browser or all remembered browsers. Password change and password reset revoke all remembered browsers. Native PHP sessions remain separate per browser.

Password reset requests always return the same message. When `APP_MAIL_FROM` is configured, the current route creates a random one-hour token, stores only its SHA-256 hash, and attempts delivery through PHP's native mail function. Outbound transport and delivery are not verified release capabilities. Reset tokens are invalidated after use and when a newer reset is requested. Do not log reset links or tokens.

`user_activity` retains guide-view activity only, not raw search queries. Account security events record registration, login/logout, password changes/resets, and privacy requests without storing IP addresses in MariaDB. Run `scripts/purge-account-data.php` on an operator-approved schedule: it removes expired reset tokens, expired remembered-browser tokens, and revoked browser records older than 30 days before activity reaches 90 days. Data export and deletion actions are requests, not automatic erasure; an operator must verify and complete them.

Run the CLI-only retention cleanup regularly in the documented local environment:

```powershell
C:\xampp\php\php.exe scripts\purge-account-data.php
```

The `oauth_account_links` table is provider-neutral preparation only. No OAuth provider is configured or required for the MVP.
