<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

use GuideMyPC\Core\View;
use GuideMyPC\Features\Dashboard\DashboardController;
use GuideMyPC\Features\Dashboard\DashboardReadModel;

require_login();

if (!refresh_current_user_authorization($conn)) {
    flash('error', 'Your account is no longer available.');
    redirect('login.php');
}

$role = current_user_role();

if ($role === null) {
    abort_request(403, 'invalid_role', 'Your account role is not valid. Contact an administrator.');
}

(new DashboardController(new DashboardReadModel($conn), new View()))->show(current_user_id(), $role);
