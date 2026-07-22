<?php

declare(strict_types=1);

namespace GuideMyPC\Core;

final class Url
{
    public static function applicationUrl(?string $baseUrl, string $path = ''): string
    {
        $baseUrl = rtrim($baseUrl ?? '', '/');

        if ($path === '') {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function assetUrl(?string $baseUrl, ?string $version, string $path): string
    {
        return self::applicationUrl($baseUrl, $path) . '?v=' . rawurlencode($version ?? '1');
    }
}
