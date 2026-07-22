# Application Conventions

GuideMyPC is migrating incrementally from a server-rendered procedural PHP application to the structure defined in [`project-structure.md`](project-structure.md). The current runtime still uses root page and action routes that load `config.php` before output; it initializes configuration, sessions, shared helpers, error handling, and the `mysqli` connection. During migration, `config.php` and `includes/` are compatibility seams, not the target location for new application code.

New structural code must follow [`project-structure.md`](project-structure.md) and preserve the legacy behavior catalogued in [`route-contracts.md`](route-contracts.md). Migration-only code belongs in `database/`, web routes must not include CLI scripts, and runtime storage remains outside the repository and web root.

## Browser Forms

Use POST for mutations, include `csrf_field()`, authorize before output, validate with shared helpers, and finish successful writes with `flash()` plus `redirect()`. Use `in_transaction()` when a write contains dependent statements that must succeed or fail together. Keep redirects relative to the application routes and never derive destinations directly from unvalidated request input.

## JSON Enhancements

Server-rendered forms remain the default. JSON is available only where a route contract explicitly documents it: the dedicated `search_suggestions.php` and `search_event.php` endpoints and narrow progressive enhancements such as the dual-mode `save_progress.php` response. These routes use `json_response()` or `abort_request()` and do not form a full-resource API.

Successful responses use a 2xx status and this shape:

```json
{
  "ok": true,
  "data": {},
  "meta": { "request_id": "random-correlation-id" }
}
```

Error responses use their stable HTTP status and bounded public error details:

```json
{
  "ok": false,
  "error": { "code": "validation_error", "message": "User-safe explanation." },
  "meta": { "request_id": "random-correlation-id" }
}
```

Do not include exception text, SQL, filesystem paths, credentials, draft content, or another user's information in responses. The same applicable validation, authorization, rate-limit, logging, and redaction behavior applies to form and JSON requests; CSRF exceptions must be explicitly recorded in the route contract.

## Errors and Logs

Use `abort_request()` for expected invalid, unauthorized, absent, or rate-limited requests. It renders an accessible HTML error page or the JSON contract as appropriate. Unexpected exceptions are logged to private application storage with a random request ID and a redacted context, then receive a generic 500 response. Do not catch an exception merely to expose its message to a browser.
