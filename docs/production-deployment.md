# Future Deployment Template

The verified-core release is validated only in the documented local XAMPP environment; production hosting is not claimed. This file records future security requirements and local packaging guidance, not proof that any public host, provider, monitoring service, backup system, or rollback process has been configured or tested. Any future deployment would require a hardened PHP host, least-privilege MariaDB, HTTPS, private runtime storage, host-managed secrets, and a public root that excludes repository source, `.env`, migrations, tasks, logs, and backups.

## Local Packaging

1. Record the exact commit and migration version used for the package.
2. Run the complete local verification gate against the isolated test database and retain non-sensitive evidence.
3. Run `composer run audit:cleanup`, then create the strict source archive with `scripts/package-source.ps1 -Commit <release-commit> -OutputPath <source.zip> -UmlDirectory <reviewed-uml-directory> -ScreenshotsDirectory <reviewed-screenshots-directory>`. The archive contains `frontend/`, runnable `backend/`, `database/`, `uml/`, `docs/` with 8-10 reviewed screenshots, README, and a SHA-256 manifest. The builder rejects a backend without its own canonical CSS and JavaScript assets; `frontend/` remains a categorized review copy and is never a runtime dependency.
4. When evaluating a future deployable artifact locally, use `scripts/package-deploy.ps1 -Commit <release-commit> -OutputPath <artifact.zip>`. It installs the locked Composer autoloader into `vendor/`; successful packaging does not validate a production host.
5. Validate a clean extraction with `scripts/verify-source-package.ps1 -PackagePath <source.zip> -Database <disposable_name_test> -ExpectedCommit <40-character-release-sha>`. It rejects extra archive roots, binds the complete unique manifest to that commit, then installs dependencies, provisions distinct disposable runtime and test databases, migrates/seeds both, runs the full suite, and starts Apache in two modes: canonical `backend/public` and a randomly named localhost package subdirectory. The package gate requires both canonical CSS files, compatibility CSS/JS rewrites, required JavaScript assets, rendered stylesheet loading, non-duplicated navigation URLs, approved category emoji without Font Awesome text, arbitrary subdirectory links, blocked package-private paths, and a 320px overflow check before it removes both databases.

The generated package-root launcher and `.htaccess` support local XAMPP extraction under `htdocs/<folder>` without a virtual host. They are a compatibility shim only: production and hardened deployments must continue to expose only `backend/public`.

## v1.1 Managed Hosting Prerequisites

The post-submission v1.1 shared-account branch adds optional remembered-browser tokens but does not make a deployment claim. A future managed PHP/MariaDB host must inject database and token-secret environment variables privately, set `SESSION_COOKIE_SECURE=true`, expose only `public/`, keep `APP_PRIVATE_PATH` outside the web root, and use HTTPS before remembered-browser login is enabled. See [`v1.1-shared-account-hosting.md`](v1.1-shared-account-hosting.md) for staging, backup/restore, and rollback evidence required before claiming hosted operation.

## Future Public-Web-Root Requirement

Any future host must place the artifact outside the web root and expose only its `public/` directory. It must preserve canonical legacy `*.php` URLs through `public/index.php`; do not point a virtual host, Alias, or Nginx `root` at the repository or artifact root. The following configurations are unvalidated reference examples.

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

## Future Rollback Requirements

Any future deployment procedure must stop if a migration, smoke check, privacy boundary, or critical security check fails. It must define artifact rollback, approved database recovery, evidence retention, and named operator responsibility before use.

The future procedure should keep the prior artifact and public-root configuration available until its smoke matrix passes. A failed activation must never be worked around by exposing the artifact root.

## Current External-Service Boundary

No AI, outbound-mail, analytics, or file-storage provider is a verified release dependency. Optional privacy-enhanced guide video embeds retain written steps as the accessible fallback and are loaded only after visitor action.
