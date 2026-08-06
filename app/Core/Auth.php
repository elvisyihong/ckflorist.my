<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const PERMISSIONS = [
        'owner' => ['admin.view', 'settings.manage', 'catalogue.manage', 'enquiries.manage', 'users.manage'],
        'manager' => ['admin.view', 'settings.manage', 'catalogue.manage', 'enquiries.manage'],
        'editor' => ['admin.view', 'catalogue.manage', 'enquiries.manage'],
        'viewer' => ['admin.view'],
    ];

    public static function user(): ?array
    {
        $user = Session::get('admin_user');
        return is_array($user) ? $user : null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('admin_user', [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ]);
        Csrf::ensureToken();
    }

    public static function logout(): void { Session::invalidate(); }

    public static function can(string $permission): bool
    {
        $role = self::user()['role'] ?? '';
        return in_array($permission, self::PERMISSIONS[$role] ?? [], true);
    }

    public static function requirePermission(string $permission): void
    {
        if (self::user() === null) {
            Response::redirect('/admin/login');
        }
        if (!self::can($permission)) {
            http_response_code(403);
            View::render('pages/error', ['requestId' => 'permission-denied'], 'layouts/admin');
            exit;
        }
    }
}

