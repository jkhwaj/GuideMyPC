<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Pages;

use GuideMyPC\Core\View;

final class PageController
{
    public function __construct(private readonly View $view)
    {
    }

    public function about(): void
    {
        $this->view->render('pages/about', [
            'page' => [
                'title' => 'About | GuideMyPC',
                'description' => 'Learn about GuideMyPC and its approach to practical technology support.',
                'canonicalPath' => 'about.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    /**
     * @return array{user: array{name: string, isAdmin: bool}|null}
     */
    private function navigation(): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['user' => null];
        }

        return [
            'user' => [
                'name' => is_string($_SESSION['full_name'] ?? null) ? $_SESSION['full_name'] : '',
                'isAdmin' => ($_SESSION['role'] ?? null) === 'admin',
            ],
        ];
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    private function flashMessages(): array
    {
        $messages = [];

        foreach (['success', 'error', 'status'] as $type) {
            $message = flash($type);

            if ($message !== null) {
                $messages[] = ['type' => $type, 'message' => $message];
            }
        }

        return $messages;
    }
}
