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

    public function contact(): void
    {
        $this->view->render('pages/contact', [
            'page' => [
                'title' => 'Contact | GuideMyPC',
                'description' => 'Find the current support options for GuideMyPC.',
                'canonicalPath' => 'contact.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    public function privacy(): void
    {
        $this->view->render('pages/privacy', [
            'page' => [
                'title' => 'Privacy | GuideMyPC',
                'description' => 'Read how GuideMyPC handles account and support information.',
                'canonicalPath' => 'privacy.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    public function terms(): void
    {
        $this->view->render('pages/terms', [
            'page' => [
                'title' => 'Terms | GuideMyPC',
                'description' => 'Read the terms for using GuideMyPC.',
                'canonicalPath' => 'terms.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    public function disclaimer(): void
    {
        $this->view->render('pages/disclaimer', [
            'page' => [
                'title' => 'Disclaimer | GuideMyPC',
                'description' => 'Understand the limits of GuideMyPC troubleshooting guidance.',
                'canonicalPath' => 'disclaimer.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    public function donate(): void
    {
        $this->view->render('pages/donate', [
            'page' => [
                'title' => 'Support GuideMyPC | GuideMyPC',
                'description' => 'Learn how to support the continued development of GuideMyPC.',
                'canonicalPath' => 'donate.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    public function ai(): void
    {
        $this->view->render('pages/ai', [
            'page' => [
                'title' => 'AI Assistant | GuideMyPC',
                'description' => 'Learn about the planned GuideMyPC AI troubleshooting assistant.',
                'canonicalPath' => 'ai.php',
            ],
            'navigation' => $this->navigation(),
            'flashMessages' => $this->flashMessages(),
        ]);
    }

    /**
     * @return array{user: array{name: string, canViewDashboard: bool, isAdmin: bool}|null}
     */
    private function navigation(): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['user' => null];
        }

        return [
            'user' => [
                'name' => is_string($_SESSION['full_name'] ?? null) ? $_SESSION['full_name'] : '',
                'canViewDashboard' => user_can(\GuideMyPC\Security\Authorization::VIEW_PERSONAL_DASHBOARD),
                'isAdmin' => is_admin(),
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
