<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;
    private static bool $attempted = false;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }
        self::$attempted = true;
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Env::get('DB_HOST', '127.0.0.1'),
            Env::get('DB_PORT', '3306'),
            Env::get('DB_DATABASE', 'ckflorist'),
        );
        self::$connection = new PDO($dsn, Env::get('DB_USERNAME'), Env::get('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        return self::$connection;
    }

    public static function available(): bool
    {
        if (self::$connection instanceof PDO) {
            return true;
        }
        if (self::$attempted) {
            return false;
        }
        try {
            self::connection()->query('SELECT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public static function reset(): void
    {
        self::$connection = null;
        self::$attempted = false;
    }
}

