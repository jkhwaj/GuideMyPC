<?php

declare(strict_types=1);

function trusted_download_url(mixed $value): ?string
{
    return (new GuideMyPC\Features\Downloads\DownloadPolicy())->trustedUrl($value);
}

function download_is_public(array $download): bool
{
    return (new GuideMyPC\Features\Downloads\DownloadPolicy())->isPublic($download);
}
