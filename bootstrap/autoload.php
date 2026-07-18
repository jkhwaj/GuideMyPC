<?php

declare(strict_types=1);

$guideMyPcRoot = dirname(__DIR__);
$composerAutoload = $guideMyPcRoot . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
    return;
}

spl_autoload_register(static function (string $class) use ($guideMyPcRoot): void {
    $prefix = 'GuideMyPC\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $path = $guideMyPcRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relativePath . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});
