# Application Conventions

GuideMyPC remains a server-rendered procedural PHP application. Root page and action routes load `config.php` before output; it initializes configuration, sessions, shared helpers, error handling, and the `mysqli` connection. Shared code belongs in `includes/`, migration-only code belongs in `database/`, and web routes must not include CLI scripts.

## Browser Forms

Use POST for mutations, include `csrf_field()`, authorize before output, validate with shared helpers, and finish successful writes with `flash()` plus `redirect()`. Use `in_transaction()` when a write contains dependent statements that must succeed or fail together. Keep redirects relative to the application routes and never derive destinations directly from unvalidated request input.

## JSON Enhancements

Server-rendered forms remain the default. An interaction may request JSON by sending `Accept: application/json`; its endpoint uses `json_response()` or `abort_request()`.

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

Do not include exception text, SQL, filesystem paths, credentials, draft content, or another user's information in responses. The same validation, authorization, CSRF, rate-limit, logging, and redaction behavior applies to form and JSON requests.

## Errors and Logs

Use `abort_request()` for expected invalid, unauthorized, absent, or rate-limited requests. It renders an accessible HTML error page or the JSON contract as appropriate. Unexpected exceptions are logged to private application storage with a random request ID and a redacted context, then receive a generic 500 response. Do not catch an exception merely to expose its message to a browser.
