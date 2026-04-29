<?php

namespace Middleware;

class Middleware
{
    public static function auth(): callable
    {
        return function (string $path, string $method): void {
            $role = SessionContext::role();
            $userId = SessionContext::userId();

            if ($role !== '' && $userId > 0) {
                return;
            }

            static::deny($path, 401, '/');
        };
    }

    public static function guest(): callable
    {
        return function (string $path, string $method): void {
            $role = SessionContext::role();
            $userId = SessionContext::userId();

            if ($role === '' || $userId < 1) {
                return;
            }

            static::redirectByRole($role);
        };
    }

    public static function role(string ...$allowedRoles): callable
    {
        return function (string $path, string $method) use ($allowedRoles): void {
            $role = SessionContext::role();
            $userId = SessionContext::userId();

            if ($role === '' || $userId < 1) {
                static::deny($path, 401, '/login');
            }

            if (in_array($role, $allowedRoles, true)) {
                return;
            }

            static::deny($path, 403, static::redirectByRoleUrl($role));
        };
    }

    public static function forAuth(): array
    {
        return [static::auth()];
    }

    public static function forGuest(): array
    {
        return [static::guest()];
    }

    public static function forCollector(): array
    {
        return [static::auth(), static::role('collector')];
    }

    public static function forAdmin(): array
    {
        return [static::auth(), static::role('admin')];
    }

    private static function isApiRoute(string $path): bool
    {
        return str_starts_with($path, '/api/');
    }

    private static function deny(string $path, int $statusCode, string $redirectUrl): void
    {
        if (static::isApiRoute($path)) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => $statusCode === 401 ? 'Unauthorized' : 'Forbidden'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    private static function redirectByRole(string $role): void
    {
        $url = static::redirectByRoleUrl($role);
        header('Location: ' . $url);
        exit;
    }

    private static function redirectByRoleUrl(string $role): string
    {
        return match ($role) {
            'collector' => '/home',
            'admin' => '/panel',
            default => '/',
        };
    }
}
