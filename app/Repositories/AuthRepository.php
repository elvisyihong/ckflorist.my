<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Env;
use PDO;

final class AuthRepository
{
    public function attempt(string $email, string $password, string $ip): ?array
    {
        if (!Database::available()) {
            return null;
        }
        $pdo = Database::connection();
        $identityHash = $this->hash(strtolower(trim($email)));
        $ipHash = $this->hash($ip);
        $count = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE identity_hash = :identity AND ip_hash = :ip AND was_successful = 0 AND attempted_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)');
        $count->execute(['identity' => $identityHash, 'ip' => $ipHash]);
        if ((int) $count->fetchColumn() >= 5) {
            throw new \RuntimeException('Too many login attempts. Try again in 15 minutes.');
        }

        $statement = $pdo->prepare('SELECT id, name, email, password_hash, role FROM admin_users WHERE email = :email AND is_active = 1 LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        $valid = is_array($user) && password_verify($password, $user['password_hash']);

        $attempt = $pdo->prepare('INSERT INTO login_attempts (identity_hash, ip_hash, was_successful) VALUES (:identity, :ip, :successful)');
        $attempt->execute(['identity' => $identityHash, 'ip' => $ipHash, 'successful' => $valid ? 1 : 0]);

        if (!$valid) {
            usleep(250000);
            return null;
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $rehash = $pdo->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id');
            $rehash->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        }
        $pdo->prepare('UPDATE admin_users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id')->execute(['id' => $user['id']]);
        unset($user['password_hash']);
        return $user;
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', $value, Env::get('APP_KEY', 'ckf-local-key'));
    }
}

