<?php

namespace Middleware;

class SessionContext
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function role(): string
    {
        static::start();
        return (string)($_SESSION['rol'] ?? '');
    }

    public static function userId(): int
    {
        static::start();
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function isAuthenticated(): bool
    {
        return static::role() !== '' && static::userId() > 0;
    }
}