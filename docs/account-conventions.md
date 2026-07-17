# Account and Activity Conventions

GuideMyPC keeps public troubleshooting content available without an account. Accounts persist favorites, guide progress, and recently viewed guide titles. Guest checklist state is held only in the browser session and is merged after successful registration or login without overwriting an existing progress record.

Password reset requests always return the same message. When `APP_MAIL_FROM` is configured, the request creates a random one-hour token, stores only its SHA-256 hash, and sends the raw token in the email link. Reset tokens are invalidated after use and when a newer reset is requested. Do not log reset links or tokens.

`user_activity` retains guide-view activity only, not raw search queries. Account security events record registration, login/logout, password changes/resets, and privacy requests without storing IP addresses in MariaDB. Run an operator-approved retention job before activity reaches 90 days. Data export and deletion actions are requests, not automatic erasure; an operator must verify and complete them.

Run the CLI-only retention cleanup regularly in local or production operations:

```powershell
C:\xampp\php\php.exe scripts\purge-account-data.php
```

The `oauth_account_links` table is provider-neutral preparation only. No OAuth provider is configured or required for the MVP.
