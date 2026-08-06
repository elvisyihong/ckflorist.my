<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\MediaRepository;
use App\Repositories\SettingsRepository;
use App\Services\MaintenanceMode;

final class MaintenanceController
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly MediaRepository $media,
        private readonly MaintenanceMode $maintenance,
    ) {}

    public function show(Request $request): void
    {
        $settings = $this->settings->all();
        $images = $this->media->findMany($this->maintenance->imageIds($settings));

        http_response_code(503);
        header('Retry-After: 3600');
        header('Cache-Control: no-store, max-age=0');
        View::render('pages/maintenance', [
            'title' => (string) ($settings['maintenance_title'] ?? 'Our menu is available'),
            'settings' => $settings,
            'images' => $images,
        ], 'layouts/maintenance');
    }
}
