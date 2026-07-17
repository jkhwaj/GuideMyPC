<?php

declare(strict_types=1);

namespace GuideMyPC\Core;

final class View
{
    public function __construct(private readonly string $viewRoot = '')
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): void
    {
        if ($view === '' || str_contains($view, '..')) {
            throw new \InvalidArgumentException('Invalid view name.');
        }

        $root = $this->viewRoot !== ''
            ? $this->viewRoot
            : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        $viewPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View file not found.');
        }

        $content = $this->renderFile($viewPath, $data);
        extract($data, EXTR_SKIP);
        require $root . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'app.php';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderFile(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}
