# Task: Accessibility, SEO, and Performance

- Status: Not started
- Priority: High
- Release: R4
- Dependencies: `005-responsive-design-system-and-layout.md` through `018-admin-and-content-moderation.md`

## Objective

Make the full public experience accessible, discoverable, fast, mobile-friendly, and respectful of privacy before release.

## Current State

The prototype has basic responsive metadata but no documented WCAG target, structured metadata, sitemap, robots policy, caching strategy, image pipeline, performance budget, or automated accessibility checks. CSS currently bypasses caching with a timestamp.

## Scope

- Complete a WCAG 2.2 AA review of all public and account-critical journeys.
- Add unique titles/descriptions, canonical links, Open Graph metadata, robots directives, and XML sitemap.
- Add appropriate schema.org data for articles, how-to content where eligible, FAQs where eligible, breadcrumbs, videos, and discussions without misrepresenting content.
- Add clean stable URLs or documented canonical handling for current PHP routes.
- Optimize CSS/JavaScript delivery, database queries, images, embeds, caching, pagination, and fonts.
- Set performance budgets for key public routes and representative mobile conditions.
- Add privacy-friendly analytics and consent behavior where legally required.
- Add custom 404 and no-index rules for private, draft, account, search-filter, and admin pages.

## Non-Goals

- Search-engine ranking guarantees
- Invalid structured data solely for rich results
- Tracking users across unrelated sites
- Performance changes without measurement

## Implementation Steps

1. Define an accessibility test matrix covering guest, account, diagnostic, AI, upload, and community flows.
2. Fix semantic, keyboard, contrast, zoom/reflow, focus, form error, status, media, and motion issues.
3. Add metadata helpers, sitemap generation, robots policy, and structured data.
4. Measure server timing, query counts, Core Web Vitals proxies, and asset weight.
5. Add caching/versioning, query/index improvements, lazy third-party embeds, and optimized image variants.
6. Configure Plausible or an equivalent privacy-friendly tool and document collected events.
7. Add automated accessibility and broken-link checks to the release suite.

## Database Changes

Add no speculative tables. Add indexes only from measured query plans. Store aggregate analytics outside the application database where practical.

## Security and Privacy

Do not include private query parameters, account data, diagnostic answers, conversations, or upload URLs in analytics or search metadata. Third-party embeds must follow consent and security policies.

## Accessibility

WCAG 2.2 AA is the acceptance target. Automated tools supplement, but do not replace, keyboard, screen-reader, zoom, and human comprehension reviews.

## Affected Files

- all public templates and CSS/JavaScript
- shared metadata/header/footer/error helpers
- `.htaccess`
- sitemap/robots routes or generated files
- analytics and automated-check configuration

## Acceptance Criteria

- [ ] Critical user journeys pass the agreed WCAG 2.2 AA review with no known critical/serious blocker.
- [ ] Public canonical pages have unique metadata and valid structured data where eligible.
- [ ] Private, draft, admin, and sensitive dynamic pages are not indexed.
- [ ] Sitemap includes only public canonical content.
- [ ] Key pages meet agreed mobile performance and asset budgets.
- [ ] Analytics collect only documented privacy-conscious events.
- [ ] No broken internal links remain in the release crawl.

## Validation

- Run automated accessibility tools, keyboard-only checks, 200%/400% zoom tests as applicable, and representative screen-reader journeys.
- Validate HTML, structured data, sitemap, canonical behavior, and robots directives.
- Measure homepage, search, guide, diagnostic, AI shell, and community pages under mobile throttling.
- Review analytics network requests for accidental sensitive data.

## Definition of Done

GuideMyPC is usable by the target audience across devices and assistive technology, exposes only intended pages to search engines, and meets documented performance/privacy budgets.
