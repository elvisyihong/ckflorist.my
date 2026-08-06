<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function ensureToken(): void
    {
        if (!is_string(Session::get('_csrf')) || strlen(Session::get('_csrf')) < 40) {
            Session::put('_csrf', bin2hex(random_bytes(32)));
        }
    }

    public static function token(): string { return (string) Session::get('_csrf', ''); }
    public static function validate(string $token): bool { return $token !== '' && hash_equals(self::token(), $token); }
}

