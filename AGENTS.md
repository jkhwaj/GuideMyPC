<!-- code-review-graph MCP tools -->
## MCP Tools: code-review-graph

**IMPORTANT: This project has a knowledge graph. ALWAYS use the
code-review-graph MCP tools BEFORE using Grep/Glob/Read to explore
the codebase.** The graph is faster, cheaper (fewer tokens), and gives
you structural context (callers, dependents, test coverage) that file
scanning cannot.

### When to use graph tools FIRST

- **Exploring code**: `semantic_search_nodes` or `query_graph` instead of Grep
- **Understanding impact**: `get_impact_radius` instead of manually tracing imports
- **Code review**: `detect_changes` + `get_review_context` instead of reading entire files
- **Finding relationships**: `query_graph` with callers_of/callees_of/imports_of/tests_for
- **Architecture questions**: `get_architecture_overview` + `list_communities`

Fall back to Grep/Glob/Read **only** when the graph doesn't cover what you need.

### Key Tools

| Tool | Use when |
| ------ | ---------- |
| `detect_changes` | Reviewing code changes — gives risk-scored analysis |
| `get_review_context` | Need source snippets for review — token-efficient |
| `get_impact_radius` | Understanding blast radius of a change |
| `get_affected_flows` | Finding which execution paths are impacted |
| `query_graph` | Tracing callers, callees, imports, tests, dependencies |
| `semantic_search_nodes` | Finding functions/classes by name or keyword |
| `get_architecture_overview` | Understanding high-level codebase structure |
| `refactor_tool` | Planning renames, finding dead code |

### Workflow

1. The graph auto-updates on file changes (via hooks).
2. Use `detect_changes` for code review.
3. Use `get_affected_flows` to understand impact.
4. Use `query_graph` pattern="tests_for" to check coverage.

## Project Structure

Before changing application structure, routing, bootstrap code, shared
security, or feature boundaries, read:

- `docs/project-structure.md`
- `docs/route-contracts.md`
- `Tasks/project-structure-migration/README.md`

Preserve documented legacy route and response contracts during the migration.
Keep runtime storage outside the repository and expose only `public/` through
the web server once task `008` is complete. Follow the active migration task
and record validation evidence before starting a dependent phase.

## Final Project Submission Readiness

Before planning or implementing final-project completion, repository cleanup,
folder reorganization, submission documents, UML, screenshots, or packaging,
read the following file in full:

- `Tasks/final-project-submission-readiness/README.md`

Treat that document as the execution plan for the final-project guide. Start by
inventorying every path returned by `git ls-files`. Classify every tracked file
before moving, merging, rewriting, or deleting it. Do not perform a directory-only
cleanup that breaks legacy routes, forms, redirects, JavaScript endpoints,
sitemap URLs, session behavior, database migrations, or package-relative paths.
Work through its phases and Definition of Done, and update its evidence files as
the implementation changes.