<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Env;

final class AuditRepository
{
    public function record(?int $userId, string $action, string $entityType, ?string $entityId, ?array $before, ?array $after, string $ip): void
    {
        $statement = Database::connection()->prepare('INSERT INTO audit_logs (admin_user_id, action, entity_type, entity_id, before_json, after_json, ip_hash) VALUES (:user, :action, :entity_type, :entity_id, :before_json, :after_json, :ip_hash)');
        $statement->execute([
            'user' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_json' => $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'after_json' => $after === null ? null : json_encode($after, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'ip_hash' => hash_hmac('sha256', $ip, Env::get('APP_KEY', 'ckf-local-key')),
        ]);
    }
}

