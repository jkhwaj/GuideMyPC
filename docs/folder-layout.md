# GuideMyPC Folder Layout

GuideMyPC uses one source tree for development and a separate generated layout for the academic submission. This keeps the application runnable while still producing the `frontend/`, `backend/`, `database/`, `uml/`, and `docs/` folders required by the course guides.

## Runtime source tree

```text
GuideMyPC/
|-- app/
|   |-- Core/
|   |-- Security/
|   `-- Features/
|-- bootstrap/
|-- config/
|-- public/
|   |-- index.php
|   |-- .htaccess
|   `-- assets/
|       |-- css/
|       `-- js/
|-- resources/
|   `-- views/
|-- routes/
|-- database/
|-- scripts/
|-- tests/
|-- docs/
|   `-- submission/
|-- uml/
|   |-- source/
|   `-- exports/
|-- Tasks/
|-- composer.json
|-- composer.lock
`-- README.md
```

Only `public/` should be exposed by Apache in the preferred local and production configuration.

## Placement rules

| Content | Repository path |
| --- | --- |
| PHP application classes | `app/` |
| Authentication and authorization policy | `app/Security/` |
| Web, CLI, and test initialization | `bootstrap/` |
| Non-secret configuration | `config/` |
| Public entry point and static files | `public/` |
| CSS, JavaScript, and public images | `public/assets/` |
| Server-rendered PHP templates | `resources/views/` |
| Route maps | `routes/` |
| Migrations, seeds, and database runners | `database/` |
| Operational commands | `scripts/` |
| Automated tests | `tests/` |
| Technical and submission documentation | `docs/` |
| Editable UML project files | `uml/source/` |
| UML PNG or PDF exports | `uml/exports/` |

Root `*.php` files and selected files under `includes/` remain temporary compatibility entry points. Do not move them as a directory-only cleanup. Remove them only after their route contracts have migrated and the full route test suite passes.

## Final submission archive

Run:

```powershell
composer run package:submission
```

The command creates `build/GuideMyPC_Submission.zip` with this structure:

```text
GuideMyPC/
|-- frontend/
|   |-- public/assets/
|   `-- resources/views/
|-- backend/
|   `-- runnable PHP source tree
|-- database/
|-- uml/
|-- docs/
|-- README.md
`-- PACKAGE-MANIFEST.txt
```

The generated archive repeats frontend and database files inside `backend/` so that `backend/` preserves the original runnable relative paths. The categorized top-level copies make the course submission easy to review. Do not edit generated copies. Edit the repository source and rebuild the archive.

Use the strict final gate after the Word documents, screenshots, and UML diagrams are ready:

```powershell
composer run package:submission:strict
```

## Files that must stay out of Git and the submission

Do not commit or package:

- `.env` or credentials
- `vendor/` and `node_modules/`
- logs, uploads, sessions, caches, or rate-limit data
- database backups or production data
- IDE folders
- generated ZIP files
