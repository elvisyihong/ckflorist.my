<?php

declare(strict_types=1);

namespace App\Services;

final class MaintenanceMode
{
    public function isEnabled(array $settings): bool
    {
        return filter_var($settings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function shouldIntercept(string $path, array $settings): bool
    {
        if (!$this->isEnabled($settings)) {
            return false;
        }

        return $path !== '/admin' && !str_starts_with($path, '/admin/');
    }

    public function imageIds(array $settings): array
    {
        $ids = $settings['maintenance_menu_images'] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        $normalized = [];
        foreach ($ids as $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0 && !in_array($id, $normalized, true)) {
                $normalized[] = $id;
            }
        }

        return $normalized;
    }
}
