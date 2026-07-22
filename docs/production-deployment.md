# Production Deployment Runbook

XAMPP is local development tooling only. Production requires a hardened PHP 8.2 host, managed or least-privilege MariaDB, HTTPS, private object/file storage, host-managed secrets, and an Apache/Nginx public root that excludes repository source, `.env`, migrations, tasks, logs, backups, and private uploads.

## Release Steps

1. Record the release commit, operator, target environment, and migration version.
2. Confirm task 020 passed for the same commit and staging data is sanitized.
3. Create and verify encrypted database and private-file backups outside the web root.
4. Create the immutable deployment artifact with `scripts/package-deploy.ps1 -Commit <release-commit> -OutputPath <artifact.zip>`. It installs locked production dependencies into `vendor/`; `scripts/package-source.ps1` remains the clean dependency-free source archive tool.
5. Enable accessible maintenance status if needed; deploy the immutable release artifact and host-managed secrets.
6. Serve only the artifact's `public/` directory through Apache or Nginx.
7. Run migrations once with a least-privilege migration account, then run smoke checks for home, search, guides, account, diagnostics, and provider-degraded paths.
8. Verify HTTPS, secure cookies, headers, blocked private paths, logs, health checks, scheduled jobs, backup monitoring, and alert ownership.
9. Record outcome, timestamps, evidence, and rollback decision in the checklist.

## Public Web Root

Deploy the release artifact outside the web root. Configure the server to expose only its `public/` directory and preserve legacy `*.php` URLs through `public/index.php`; do not point a virtual host, Alias, or Nginx `root` at the repository/artifact root.

Apache example:

```apache
<VirtualHost *:443>
    ServerName example.test
    DocumentRoot "/srv/guidemypc/current/public"

    <Directory "/srv/guidemypc/current/public">
        AllowOverride None
        Require all granted
        Options -Indexes
    </Directory>

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    RewriteRule ^ /index.php [L,QSA]
</VirtualHost>
```

When `AllowOverride None` is used, keep the rewrite rules in the virtual-host configuration as shown. Alternatively, enable `mod_rewrite` and permit the committed `public/.htaccess` with `AllowOverride FileInfo Options`.

Nginx example:

```nginx
server {
    server_name example.test;
    root /srv/guidemypc/current/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ \.php$ {
        rewrite ^ /index.php last;
    }
}
```

Adjust the PHP-FPM socket for the host. The exact `index.php` location is the only PHP file executed by Nginx; legacy route requests are dispatched by the front controller.

## Rollback

Stop if a migration, smoke check, privacy boundary, or critical security check fails. Restore the previous application artifact first. Restore a database/private-file backup only when the migration cannot be safely forward-fixed and the recovery point has been approved. Record the incident, affected commit, migration version, operator, and recovery outcome.

Keep the prior release artifact and its public-root server configuration available until the smoke matrix passes. To roll back a failed public-root activation, repoint the server's `DocumentRoot` or Nginx `root` to the prior artifact's `public/` directory, reload the server, and rerun the private-path and legacy-route checks. Do not work around a failure by exposing the artifact root.

## Provider Degradation

If AI, mail, analytics, storage, or monitoring fails, do not expose provider errors or retry indefinitely. Keep guides, search, diagnostics, and community available where safe; show a clear accessible fallback message; alert the assigned owner; and avoid sending queued private data until the provider is healthy.
