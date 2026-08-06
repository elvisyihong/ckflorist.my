<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AdminResourceRepository
{
    public function __construct(private readonly AuditRepository $audit) {}

    public function config(string $resource): array
    {
        $resources = require BASE_PATH . '/config/admin_resources.php';
        if (!isset($resources[$resource])) {
            throw new \InvalidArgumentException('Unknown admin resource.');
        }
        return $resources[$resource];
    }

    public function paginate(string $resource, int $page = 1): array
    {
        $config = $this->config($resource);
        $page = max(1, $page);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $columns = array_values(array_unique(array_merge(['id'], $config['list'])));
        $sql = sprintf('SELECT %s FROM %s ORDER BY %s LIMIT %d OFFSET %d', implode(', ', $columns), $config['table'], $config['order'] ?? 'id DESC', $perPage, $offset);
        $records = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $total = (int) Database::connection()->query('SELECT COUNT(*) FROM ' . $config['table'])->fetchColumn();
        return compact('records', 'total', 'page', 'perPage');
    }

    public function find(string $resource, int $id): ?array
    {
        $config = $this->config($resource);
        $statement = Database::connection()->prepare('SELECT * FROM ' . $config['table'] . ' WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($record) ? $record : null;
    }

    public function create(string $resource, array $input, int $userId, string $ip): int
    {
        $config = $this->config($resource);
        $data = $this->validate($config, $input);
        if ($data === []) {
            throw new \InvalidArgumentException('No editable fields were supplied.');
        }
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $config['table'], implode(', ', $columns), implode(', ', $placeholders));
        $statement = Database::connection()->prepare($sql);
        $statement->execute($data);
        $id = (int) Database::connection()->lastInsertId();
        $this->audit->record($userId, 'create', $resource, (string) $id, null, $data, $ip);
        return $id;
    }

    public function update(string $resource, int $id, array $input, int $userId, string $ip): void
    {
        $config = $this->config($resource);
        $before = $this->find($resource, $id);
        if (!$before) {
            throw new \InvalidArgumentException('Record not found.');
        }
        $data = $this->validate($config, $input);
        $sets = array_map(static fn (string $column): string => "{$column} = :{$column}", array_keys($data));
        $data['id'] = $id;
        $statement = Database::connection()->prepare('UPDATE ' . $config['table'] . ' SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $statement->execute($data);
        $this->audit->record($userId, 'update', $resource, (string) $id, $before, $this->find($resource, $id), $ip);
    }

    public function delete(string $resource, int $id, int $userId, string $ip): void
    {
        $config = $this->config($resource);
        $before = $this->find($resource, $id);
        if (!$before) {
            return;
        }
        if (isset($config['fields']['is_active'])) {
            $statement = Database::connection()->prepare('UPDATE ' . $config['table'] . ' SET is_active = 0 WHERE id = :id');
        } else {
            $statement = Database::connection()->prepare('DELETE FROM ' . $config['table'] . ' WHERE id = :id');
        }
        $statement->execute(['id' => $id]);
        $this->audit->record($userId, 'delete', $resource, (string) $id, $before, null, $ip);
    }

    private function validate(array $config, array $input): array
    {
        $data = [];
        foreach ($config['fields'] as $name => $field) {
            if (($field['readonly'] ?? false) || !array_key_exists($name, $input)) {
                continue;
            }
            $value = $input[$name];
            if (($field['type'] ?? 'text') === 'checkbox') {
                $value = $value ? 1 : 0;
            } elseif (($field['type'] ?? '') === 'number') {
                $value = $value === '' ? null : (float) $value;
            } else {
                $value = trim((string) $value);
                if (($field['required'] ?? false) && $value === '') {
                    throw new \InvalidArgumentException(($field['label'] ?? $name) . ' is required.');
                }
                if (mb_strlen($value) > ($field['max'] ?? 5000)) {
                    throw new \InvalidArgumentException(($field['label'] ?? $name) . ' is too long.');
                }
            }
            $data[$name] = $value;
        }
        return $data;
    }
}

