<?php

declare(strict_types=1);

namespace GuideMyPC\Core;

final class Url
{
    public static function applicationUrl(?string $baseUrl, string $path = '', string $requestBasePath = ''): string
    {
        $baseUrl = rtrim($baseUrl ?? '', '/');
        $requestBasePath = rtrim($requestBasePath, '/');

        if ($path === '') {
            return $baseUrl !== '' ? $baseUrl : $requestBasePath;
        }

        if ($baseUrl !== '') {
            return $baseUrl . '/' . ltrim($path, '/');
        }

        return ($requestBasePath === '' ? '' : $requestBasePath) . '/' . ltrim($path, '/');
    }

    public static function assetUrl(?string $baseUrl, ?string $version, string $path, string $requestBasePath = ''): string
    {
        return self::applicationUrl($baseUrl, $path, $requestBasePath) . '?v=' . rawurlencode($version ?? '1');
    }
}
