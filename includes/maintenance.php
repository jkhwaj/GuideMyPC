<?php

declare(strict_types=1);

function maintenance_cadences(): array
{
    return ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'event' => 'When needed'];
}

function maintenance_resource_url(array $recommendation): ?string
{
    if (!empty($recommendation['guide_slug'])) return application_url('guide.php?slug=' . rawurlencode($recommendation['guide_slug']));
    if (!empty($recommendation['knowledge_slug'])) return application_url('knowledge_article.php?slug=' . rawurlencode($recommendation['knowledge_slug']));
    return null;
}
