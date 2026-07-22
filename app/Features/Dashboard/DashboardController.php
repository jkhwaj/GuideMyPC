<?php

declare(strict_types=1);

namespace GuideMyPC\Features\Dashboard;

use GuideMyPC\Core\View;
use GuideMyPC\Security\Authorization;

final class DashboardController
{
    public function __construct(
        private readonly DashboardReadModel $readModel,
        private readonly View $view
    ) {
    }

    public function show(int $userId, string $role): void
    {
        $isOperational = Authorization::allows($role, Authorization::VIEW_CONTENT_DASHBOARD);
        $dashboard = $isOperational
            ? $this->readModel->operational(Authorization::allows($role, Authorization::VIEW_AUDIT))
            : $this->readModel->personal($userId);

        $this->view->render('pages/dashboard', [
            'page' => [
                'title' => 'Dashboard | GuideMyPC',
                'description' => 'Review your GuideMyPC activity and role-appropriate content metrics.',
                'canonicalPath' => 'dashboard.php',
                'scripts' => $isOperational ? [[
                    'src' => asset_url('js/chart.umd.min.js'),
                ]] : [],
            ],
            'navigation' => [
                'user' => [
                    'name' => is_string($_SESSION['full_name'] ?? null) ? $_SESSION['full_name'] : '',
                    'canViewDashboard' => Authorization::allows($role, Authorization::VIEW_PERSONAL_DASHBOARD),
                    'isAdmin' => Authorization::hasRole($role, 'admin'),
                ],
            ],
            'flashMessages' => $this->flashMessages(),
            'dashboard' => $dashboard,
            'role' => $role,
        ]);
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
