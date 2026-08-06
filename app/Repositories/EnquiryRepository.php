<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Env;
use App\Services\ReferenceGenerator;
use PDO;
use Throwable;

final class EnquiryRepository
{
    public function __construct(private readonly ReferenceGenerator $references) {}

    public function create(array $data, string $ip): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $reference = $this->references->generate(function (string $candidate) use ($pdo): bool {
                $statement = $pdo->prepare('SELECT 1 FROM enquiries WHERE reference = :reference');
                $statement->execute(['reference' => $candidate]);
                return (bool) $statement->fetchColumn();
            });
            $statement = $pdo->prepare('INSERT INTO enquiries (reference, customer_name, customer_phone, customer_email, fulfilment_method, requested_date, requested_time, delivery_address, occasion_id, bouquet_snapshot, cafe_snapshot, estimated_total_min, estimated_total_max, customer_notes, consented_at, whatsapp_opened_at, source_ip_hash) VALUES (:reference, :customer_name, :customer_phone, :customer_email, :fulfilment_method, :requested_date, :requested_time, :delivery_address, :occasion_id, :bouquet_snapshot, :cafe_snapshot, :estimated_total_min, :estimated_total_max, :customer_notes, UTC_TIMESTAMP(), UTC_TIMESTAMP(), :ip_hash)');
            $statement->execute([
                'reference' => $reference,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?: null,
                'fulfilment_method' => $data['fulfilment_method'],
                'requested_date' => $data['requested_date'],
                'requested_time' => $data['requested_time'] ?: null,
                'delivery_address' => $data['delivery_address'] ?: null,
                'occasion_id' => $data['occasion_id'] ?: null,
                'bouquet_snapshot' => $data['bouquet'] ? json_encode($data['bouquet'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null,
                'cafe_snapshot' => $data['cafe'] ? json_encode($data['cafe'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null,
                'estimated_total_min' => $data['estimated_total_min'],
                'estimated_total_max' => $data['estimated_total_max'],
                'customer_notes' => $data['customer_notes'] ?: null,
                'ip_hash' => hash_hmac('sha256', $ip, Env::get('APP_KEY', 'ckf-local-key')),
            ]);
            $id = (int) $pdo->lastInsertId();
            $event = $pdo->prepare("INSERT INTO enquiry_events (enquiry_id, event_type, note) VALUES (:id, 'whatsapp_opened', 'WhatsApp handoff link generated; order remains unconfirmed.')");
            $event->execute(['id' => $id]);
            $pdo->commit();
            return ['id' => $id, 'reference' => $reference] + $data;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function findByReference(string $reference): ?array
    {
        if (!Database::available()) {
            return null;
        }
        $statement = Database::connection()->prepare('SELECT * FROM enquiries WHERE reference = :reference LIMIT 1');
        $statement->execute(['reference' => $reference]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($record)) {
            return null;
        }
        $record['bouquet_snapshot'] = json_decode((string) ($record['bouquet_snapshot'] ?? 'null'), true);
        $record['cafe_snapshot'] = json_decode((string) ($record['cafe_snapshot'] ?? 'null'), true);
        return $record;
    }

    public function recent(int $limit = 10): array
    {
        $limit = max(1, min(100, $limit));
        return Database::connection()->query("SELECT id, reference, status, customer_name, customer_phone, requested_date, fulfilment_method, created_at FROM enquiries ORDER BY created_at DESC LIMIT {$limit}")->fetchAll();
    }

    public function dashboardCounts(): array
    {
        $rows = Database::connection()->query("SELECT status, COUNT(*) total FROM enquiries GROUP BY status")->fetchAll();
        return array_column($rows, 'total', 'status');
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM enquiries WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $record = $statement->fetch();
        return is_array($record) ? $record : null;
    }

    public function updateStatus(int $id, string $status, int $userId, string $note = ''): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $record = $this->find($id);
            if (!$record) {
                throw new \InvalidArgumentException('Enquiry not found.');
            }
            $pdo->prepare('UPDATE enquiries SET status = :status WHERE id = :id')->execute(['status' => $status, 'id' => $id]);
            $pdo->prepare("INSERT INTO enquiry_events (enquiry_id, event_type, from_status, to_status, note, actor_admin_id) VALUES (:id, 'status_changed', :from_status, :to_status, :note, :actor)")->execute(['id' => $id, 'from_status' => $record['status'], 'to_status' => $status, 'note' => $note ?: null, 'actor' => $userId]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

}
