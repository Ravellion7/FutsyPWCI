<?php

class Database
{
    private static ?Database $instance = null;
    private static bool $envLoaded = false;
    private mysqli $connection;

    private function __construct()
    {
        self::loadEnvFile();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = self::getEnvValue('DB_HOST') ?? '127.0.0.1';
        $user = self::getEnvValue('DB_USER');
        $password = self::getEnvValue('DB_PASSWORD');
        $database = self::getEnvValue('DB_NAME');

        if ($user === null || $database === null) {
            exit('Database connection error: missing required environment variables DB_USER and/or DB_NAME.');
        }

        try {
            $this->connection = new mysqli($host, $user, $password ?? '', $database);
            $this->connection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            exit('Database connection error: ' . $e->getMessage());
        }
    }

    private static function loadEnvFile(): void
    {
        if (self::$envLoaded) {
            return;
        }

        self::$envLoaded = true;
        $envPath = dirname(__DIR__) . '/.env';
        if (!is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);

            if ($value !== '' && (
                ($value[0] === '"' && substr($value, -1) === '"') ||
                ($value[0] === "'" && substr($value, -1) === "'")
            )) {
                $value = substr($value, 1, -1);
            }

            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    private static function getEnvValue(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }

        return $value === '' ? null : $value;
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton Database instance.');
    }
}
