<?php

declare(strict_types=1);

namespace GuideMyPC\Security;

final class Authorization
{
    public const VIEW_PERSONAL_DASHBOARD = 'dashboard.personal.view';
    public const VIEW_CONTENT_DASHBOARD = 'dashboard.content.view';
    public const MANAGE_CONTENT = 'content.manage';
    public const PUBLISH_CONTENT = 'content.publish';
    public const MODERATE_COMMUNITY = 'community.moderate';
    public const VIEW_REPORTS = 'reports.view';
    public const DELETE_CONTENT = 'content.delete';
    public const MANAGE_USERS = 'users.manage';
    public const VIEW_AUDIT = 'audit.view';
    public const CONFIGURE_SYSTEM = 'system.configure';

    /** @var list<string> */
    private const ROLES = ['user', 'editor', 'admin'];

    /** @var array<string, list<string>> */
    private const CAPABILITIES = [
        'user' => [
            self::VIEW_PERSONAL_DASHBOARD,
        ],
        'editor' => [
            self::VIEW_PERSONAL_DASHBOARD,
            self::VIEW_CONTENT_DASHBOARD,
            self::MANAGE_CONTENT,
            self::PUBLISH_CONTENT,
            self::MODERATE_COMMUNITY,
            self::VIEW_REPORTS,
        ],
        'admin' => [
            self::VIEW_PERSONAL_DASHBOARD,
            self::VIEW_CONTENT_DASHBOARD,
            self::MANAGE_CONTENT,
            self::PUBLISH_CONTENT,
            self::MODERATE_COMMUNITY,
            self::VIEW_REPORTS,
            self::DELETE_CONTENT,
            self::MANAGE_USERS,
            self::VIEW_AUDIT,
            self::CONFIGURE_SYSTEM,
        ],
    ];

    public static function normalizeRole(mixed $role): ?string
    {
        if (!is_string($role)) {
            return null;
        }

        $role = trim($role);

        return in_array($role, self::ROLES, true) ? $role : null;
    }

    public static function allows(mixed $role, string $capability): bool
    {
        $normalizedRole = self::normalizeRole($role);

        return $normalizedRole !== null
            && in_array($capability, self::CAPABILITIES[$normalizedRole], true);
    }

    public static function hasRole(mixed $role, string $expectedRole): bool
    {
        return self::normalizeRole($role) === $expectedRole;
    }
}
