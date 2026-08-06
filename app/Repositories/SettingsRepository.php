<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Support\DemoData;
use PDO;

final class SettingsRepository
{
    private ?array $cache = null;

    public function all(bool $publicOnly = true): array
    {
        if ($this->cache !== null && $publicOnly) {
            return $this->cache;
        }
        if (!Database::available()) {
            return $this->cache = DemoData::settings();
        }
        $cachePath = BASE_PATH . '/storage/cache/shop-settings.json';
        if ($publicOnly && is_file($cachePath) && filemtime($cachePath) > time() - 300) {
            $cached = json_decode((string) file_get_contents($cachePath), true);
            if (is_array($cached)) return $this->cache = $cached;
        }
        $sql = 'SELECT setting_key, setting_value, value_type FROM shop_settings' . ($publicOnly ? ' WHERE is_public = 1' : '');
        $rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = match ($row['value_type']) {
                'boolean' => filter_var($row['setting_value'], FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $row['setting_value'],
                'json' => json_decode((string) $row['setting_value'], true) ?: [],
                default => $row['setting_value'],
            };
        }
        if ($publicOnly) {
            $temporary = $cachePath . '.' . bin2hex(random_bytes(4));
            if (file_put_contents($temporary, json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false) {
                rename($temporary, $cachePath);
            }
            return $this->cache = $settings;
        }
        return $settings;
    }

    public function save(array $input, int $userId): void
    {
        $allowed = ['business_name','logo','favicon','whatsapp_number','telephone','email','address','google_maps_url','map_embed','business_hours','social_links','currency','delivery_information','pickup_information','florist_disclaimer','cancellation_policy','terms','privacy_policy','allow_combined_enquiries'];
        $statement = Database::connection()->prepare('INSERT INTO shop_settings (setting_key, setting_value, value_type, is_public, updated_by) VALUES (:key, :value, :type, 1, :user) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value_type = VALUES(value_type), updated_by = VALUES(updated_by)');
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $value = $input[$key];
            $type = in_array($key, ['business_hours','social_links'], true) ? 'json' : ($key === 'allow_combined_enquiries' ? 'boolean' : (mb_strlen((string) $value) > 190 ? 'text' : 'string'));
            if ($type === 'json') {
                $value = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
            $statement->execute(['key' => $key, 'value' => (string) $value, 'type' => $type, 'user' => $userId]);
        }
        $this->cache = null;
        $cachePath = BASE_PATH . '/storage/cache/shop-settings.json';
        if (is_file($cachePath)) unlink($cachePath);
    }
}
