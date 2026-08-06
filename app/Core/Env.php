<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
            if ($key === '' || getenv($key) !== false) {
                continue;
            }
            $value = trim($value, "\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): string
    {
        $value = getenv($key);
        return $value === false ? (string) $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = getenv($key);
        return $value === false ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

