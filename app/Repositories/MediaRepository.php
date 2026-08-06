<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class MediaRepository
{
    public function findMany(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === [] || !Database::available()) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare(
            "SELECT id, path, thumbnail_path, width, height, alt_text FROM media WHERE id IN ({$placeholders})"
        );
        $statement->execute($ids);

        $records = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $record) {
            $record['id'] = (int) $record['id'];
            $record['path'] = '/' . ltrim((string) $record['path'], '/');
            $record['thumbnail'] = '/' . ltrim((string) ($record['thumbnail_path'] ?: $record['path']), '/');
            $records[$record['id']] = $record;
        }

        return array_values(array_filter(array_map(
            static fn (int $id): ?array => $records[$id] ?? null,
            $ids,
        )));
    }

    public function existingIds(array $ids): array
    {
        return array_column($this->findMany($ids), 'id');
    }
}
