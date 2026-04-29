<?php

namespace App\Services;

class EnvLoader
{
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(string $filePath = ''): void
    {
        if (self::$loaded) {
            return;
        }

        // If no path provided, use default from project root
        if (empty($filePath)) {
            $filePath = __DIR__ . '/../../.env';
        }

        if (!file_exists($filePath)) {
            self::$loaded = true;
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                self::$vars[trim($key)] = trim($value);
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }
}
