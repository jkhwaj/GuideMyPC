# GuideMyPC System Overview

## Abstract

GuideMyPC is a PHP and MariaDB consumer-technology support application. The implemented release connects published support guides, knowledge articles, search, account progress, diagnostics, trusted-resource foundations, and moderated-content foundations through safety-conscious server-rendered workflows.

## Problem and Audience

Everyday users encounter fragmented, inconsistent, and sometimes unsafe support advice. GuideMyPC targets beginner through advanced users who need understandable troubleshooting steps and clear escalation boundaries.

## Capabilities and Value

Implemented public capabilities include searchable published content, structured repair guides, account-backed progress, a diagnostic foundation, and reviewed-resource models. The unique value is one connected path from a symptom to explainable next actions and reviewed resources. Incomplete roadmap foundations must be labeled future work in the final report.

## Architecture

The frontend is server-rendered HTML with responsive custom CSS and progressive vanilla JavaScript. The current PHP 8.2 runtime uses root routes with shared procedural bootstrap, security, validation, and helper layers; it is being migrated incrementally to a feature-oriented structure without a framework rewrite. MariaDB 10.4 uses `mysqli`, prepared statements, versioned migrations, and sanitized seeds. XAMPP is local-only; production requires hardened PHP hosting. The migration target exposes only `public/`, keeps runtime storage private, and preserves legacy route contracts until separately approved URL work. Alternatives such as an API-first JavaScript rewrite were rejected because incremental PHP changes preserve the working prototype while reducing delivery risk.
