# Task: XAMPP Local Setup

- Status: Not started
- Priority: Critical
- Release: R0
- Dependencies: `000-product-scope-and-architecture.md`

## Objective

Make a clean GuideMyPC installation reproducible on Windows with XAMPP, without relying on undocumented machine-specific settings.

## Current State

The repository is expected at `C:\xampp\htdocs\GuideMyPC`, uses Apache and MariaDB defaults, and connects with hard-coded credentials in `config.php`. `README.md` is empty and there is no environment example or setup check.

## Scope

- Document supported XAMPP, PHP, Apache, and MariaDB versions.
- Add prerequisites, clone/install, Apache modules, database creation, migration, seed, and startup instructions.
- Add an environment template containing only safe example values.
- Document the local URL, optional virtual-host setup, file permissions, mail limitations, and troubleshooting.
- Add a simple non-public setup or health-check command that verifies required PHP extensions and database connectivity.
- Document how to reset development data safely.

## Non-Goals

- Production deployment
- Docker support
- Automated installation of XAMPP
- Committing local credentials

## Implementation Steps

1. Replace the empty root `README.md` with project purpose, prerequisites, and links to detailed setup.
2. Add `.env.example` and ignore `.env`, logs, user uploads, test caches, and local IDE files.
3. Document required extensions: `mysqli`, `mbstring`, `openssl`, `fileinfo`, `json`, and `curl`.
4. Add Composer installation steps if task `004` introduces Composer.
5. Document migration and seed commands from task `003`.
6. Add common fixes for Apache port conflicts, MariaDB startup failures, and missing extensions.

## Database Changes

No schema changes. Setup must create a UTF-8 `guidemypc` database and invoke versioned migrations rather than importing a mutable production-like dump.

## Security and Privacy

- Never instruct users to expose phpMyAdmin publicly.
- Never place real API keys or passwords in documentation.
- Development defaults must not be described as suitable for production.

## Accessibility

Not applicable to setup UI. Documentation must use clear headings, ordered procedures, and copyable commands.

## Affected Files

- `README.md`
- `.gitignore`
- `.env.example`
- setup/health-check script selected during implementation
- `Tasks/web-init/001-xampp-local-setup.md`

## Acceptance Criteria

- [ ] A new developer can run GuideMyPC at `http://localhost/GuideMyPC/` from a clean supported XAMPP installation.
- [ ] Setup includes database migration and optional sample-content seeding.
- [ ] No local secret or generated upload is tracked.
- [ ] Required extensions and common XAMPP failures are documented.
- [ ] A setup check reports actionable failures without exposing credentials.

## Validation

- Follow the README on a clean database.
- Run the setup check once with valid configuration and once with a deliberately invalid database name.
- Confirm `git status` does not include `.env`, logs, or uploads.

## Definition of Done

A developer unfamiliar with the repository can install, configure, verify, and reset the local application using only tracked documentation.
