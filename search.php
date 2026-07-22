<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/search.php';

$filters = search_filters($_GET);
$enteredQuery = $filters['query'];
$resolvedQuery = search_resolve_alias($conn, $enteredQuery);
$filters['query'] = $resolvedQuery;
$filterOptions = search_filter_options($conn);
$allResults = [];
$pageResults = [];
$relatedQueries = [];
$totalResults = 0;
$searchElapsedMilliseconds = 0;

if ($resolvedQuery !== '') {
    $startedAt = hrtime(true);
    $allResults = search_documents($conn, $filters);
    $searchElapsedMilliseconds = (int) ((hrtime(true) - $startedAt) / 1_000_000);
    $totalResults = count($allResults);
    $pagination = search_result_pagination($totalResults, $filters['page']);
    $filters['page'] = $pagination['page'];
    $pageResults = array_slice($allResults, $pagination['offset'], $pagination['per_page']);
    $relatedQueries = search_related_queries($conn, $resolvedQuery);
    record_search_event($conn, $resolvedQuery, $totalResults, 'search', $totalResults === 0 ? 'zero' : 'results');

    if ($searchElapsedMilliseconds > 250) {
        application_log('warning', 'Search response exceeded the local response-time budget.', ['elapsed_ms' => $searchElapsedMilliseconds, 'result_count' => $totalResults]);
    }
}

$queryParameters = static function (array $overrides = []) use ($filters, $enteredQuery): string {
    $parameters = [
        'q' => $enteredQuery,
        'type' => $filters['type'],
        'platform' => $filters['platform'],
        'difficulty' => $filters['difficulty'],
        'safety' => $filters['safety'],
        'recency' => $filters['recency'],
    ];
    $parameters = array_filter(array_merge($parameters, $overrides), static fn (mixed $value): bool => $value !== '' && $value !== null);

    return application_url('search.php' . ($parameters === [] ? '' : '?' . http_build_query($parameters)));
};

$pageTitle = $enteredQuery === '' ? 'Search Support | GuideMyPC' : 'Search: ' . $enteredQuery . ' | GuideMyPC';
$pageDescription = 'Search GuideMyPC guides, official downloads, and published community questions by problem, device, or error.';
$canonicalPath = 'search.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="section" aria-labelledby="search-heading">
    <p class="section-label">Support Search</p>
    <h1 id="search-heading">Find a trusted next step</h1>
    <p class="section-desc">Search published guides, knowledge articles, official downloads, and community questions. Exact titles and error codes appear before broader matches.</p>

    <form class="home-search search-page-form" action="<?php echo e(application_url('search.php')); ?>" method="GET" role="search" data-search-autocomplete data-suggestion-list="search-suggestions" data-suggestion-url="<?php echo e(application_url('search_suggestions.php')); ?>">
        <label for="support-search">Describe your problem</label>
        <div class="home-search-controls">
            <input id="support-search" name="q" type="search" maxlength="120" value="<?php echo e($enteredQuery); ?>" placeholder="Example: laptop is slow" required autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="search-suggestions">
            <button class="primary-btn" type="submit">Search support</button>
        </div>
        <div id="search-suggestions" class="search-suggestions" role="listbox" hidden></div>
    </form>

    <form class="search-filters" method="GET" aria-label="Filter search results">
        <input type="hidden" name="q" value="<?php echo e($enteredQuery); ?>">
        <div>
            <label for="search-type">Content type</label>
            <select id="search-type" name="type">
                <option value="">All content</option>
                <option value="guide"<?php echo $filters['type'] === 'guide' ? ' selected' : ''; ?>>Guides</option>
                <option value="download"<?php echo $filters['type'] === 'download' ? ' selected' : ''; ?>>Official downloads</option>
                <option value="community"<?php echo $filters['type'] === 'community' ? ' selected' : ''; ?>>Community questions</option>
                <option value="article"<?php echo $filters['type'] === 'article' ? ' selected' : ''; ?>>Knowledge articles</option>
            </select>
        </div>
        <div>
            <label for="search-platform">Platform</label>
            <select id="search-platform" name="platform">
                <option value="">All platforms</option>
                <?php foreach ($filterOptions['platforms'] as $platform): ?>
                    <option value="<?php echo e($platform['value']); ?>"<?php echo $filters['platform'] === $platform['value'] ? ' selected' : ''; ?>><?php echo e($platform['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="search-difficulty">Difficulty</label>
            <select id="search-difficulty" name="difficulty">
                <option value="">Any difficulty</option>
                <?php foreach ($filterOptions['difficulties'] as $difficulty): ?>
                    <option value="<?php echo e($difficulty); ?>"<?php echo $filters['difficulty'] === $difficulty ? ' selected' : ''; ?>><?php echo e($difficulty); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="search-safety">Safety level</label>
            <select id="search-safety" name="safety">
                <option value="">Any safety level</option>
                <?php foreach ($filterOptions['safety_levels'] as $safetyLevel): ?>
                    <option value="<?php echo e($safetyLevel); ?>"<?php echo $filters['safety'] === $safetyLevel ? ' selected' : ''; ?>><?php echo e($safetyLevel); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="search-recency">Recency</label>
            <select id="search-recency" name="recency">
                <option value="">Any time</option>
                <option value="30"<?php echo $filters['recency'] === '30' ? ' selected' : ''; ?>>Past 30 days</option>
                <option value="90"<?php echo $filters['recency'] === '90' ? ' selected' : ''; ?>>Past 90 days</option>
            </select>
        </div>
        <button type="submit" class="secondary-btn">Apply filters</button>
        <?php if ($enteredQuery !== '' && ($filters['type'] !== '' || $filters['platform'] !== '' || $filters['difficulty'] !== '' || $filters['safety'] !== '' || $filters['recency'] !== '')): ?>
            <a href="<?php echo e(application_url('search.php?q=' . rawurlencode($enteredQuery))); ?>">Clear filters</a>
        <?php endif; ?>
    </form>

    <?php if ($enteredQuery === ''): ?>
        <div class="content-empty"><p>Enter a problem above, or <a href="<?php echo e(application_url('guides.php')); ?>">browse all guides</a>.</p></div>
    <?php elseif ($pageResults !== []): ?>
        <p class="search-summary" role="status" aria-live="polite">
            <?php echo (int) $totalResults; ?> result<?php echo $totalResults === 1 ? '' : 's'; ?> for <strong><?php echo e($enteredQuery); ?></strong>
            <?php if ($resolvedQuery !== $enteredQuery): ?>
                <span>Searching for <strong><?php echo e($resolvedQuery); ?></strong>.</span>
            <?php endif; ?>
        </p>
        <div class="search-results" data-search-event-url="<?php echo e(application_url('search_event.php')); ?>" data-search-query="<?php echo e($resolvedQuery); ?>">
            <?php foreach ($pageResults as $result): ?>
                <article class="search-result-card">
                    <p class="eyebrow"><?php echo e($result['label']); ?> · <?php echo e($result['platform']); ?></p>
                    <h2><a href="<?php echo e($result['url']); ?>"<?php echo $result['type'] === 'download' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> data-search-selection data-search-result-type="<?php echo e($result['type']); ?>"><?php echo e($result['title']); ?></a></h2>
                    <p><?php echo search_excerpt((string) $result['excerpt'], $resolvedQuery); ?></p>
                    <?php if ($result['type'] === 'guide'): ?>
                        <p class="meta"><?php echo e((string) ($result['difficulty'] ?: 'Practical')); ?> · <?php echo e((string) ($result['safety'] ?: 'Low')); ?> risk</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalResults > 10): ?>
            <nav class="pagination" aria-label="Search result pages">
                <?php if ($filters['page'] > 1): ?><a class="secondary-btn" href="<?php echo e($queryParameters(['page' => $filters['page'] - 1])); ?>">Previous</a><?php endif; ?>
                <span>Page <?php echo (int) $filters['page']; ?> of <?php echo (int) $pagination['total_pages']; ?></span>
                <?php if ($filters['page'] < $pagination['total_pages']): ?><a class="secondary-btn" href="<?php echo e($queryParameters(['page' => $filters['page'] + 1])); ?>">Next</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="content-empty">
            <h2>No published content matched that search</h2>
            <p>Try a shorter phrase, remove a filter, use a device name, or browse all guides.</p>
            <a class="secondary-btn" href="<?php echo e(application_url('guides.php')); ?>">Browse all guides</a>
        </div>
    <?php endif; ?>

    <?php if ($relatedQueries !== []): ?>
        <aside class="related-searches" aria-labelledby="related-searches-heading">
            <h2 id="related-searches-heading">Try a related search</h2>
            <ul>
                <?php foreach ($relatedQueries as $relatedQuery): ?>
                    <li><a href="<?php echo e(application_url('search.php?q=' . rawurlencode($relatedQuery))); ?>"><?php echo e($relatedQuery); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php';
