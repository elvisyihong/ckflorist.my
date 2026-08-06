<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AuditRepository;
use App\Repositories\MediaRepository;
use App\Repositories\SettingsRepository;
use App\Services\MaintenanceMode;

final class MaintenanceController
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly MediaRepository $media,
        private readonly MaintenanceMode $maintenance,
        private readonly AuditRepository $audit,
    ) {}

    public function edit(Request $request): void
    {
        $settings = $this->settings->all(false);
        View::render('admin/maintenance', [
            'title' => 'Maintenance mode',
            'settings' => $settings,
            'images' => $this->media->findMany($this->maintenance->imageIds($settings)),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $rawIds = $request->input('maintenance_menu_images', '[]');
        $decodedIds = is_string($rawIds) ? json_decode($rawIds, true) : $rawIds;
        $ids = $this->media->existingIds(is_array($decodedIds) ? $decodedIds : []);
        $enabled = $request->input('maintenance_mode') !== null;
        $title = trim((string) $request->input('maintenance_title', 'Our menu is available'));
        $message = trim((string) $request->input('maintenance_message', 'Browse our current menu while we prepare the full website.'));

        if ($enabled && $ids === []) {
            Session::flash('error', 'Upload at least one menu image before enabling maintenance mode.');
            Response::redirect('/admin/maintenance');
        }
        if ($title === '' || mb_strlen($title) > 120) {
            Session::flash('error', 'The maintenance title must be between 1 and 120 characters.');
            Response::redirect('/admin/maintenance');
        }
        if (mb_strlen($message) > 500) {
            Session::flash('error', 'The maintenance message cannot exceed 500 characters.');
            Response::redirect('/admin/maintenance');
        }

        $before = $this->settings->all(false);
        $user = Auth::user();
        $this->settings->save([
            'maintenance_mode' => $enabled ? '1' : '0',
            'maintenance_title' => $title,
            'maintenance_message' => $message,
            'maintenance_menu_images' => $ids,
        ], (int) $user['id']);
        $after = $this->settings->all(false);
        $this->audit->record((int) $user['id'], 'update', 'maintenance_settings', null, $before, $after, $request->ip());

        Session::flash('success', $enabled ? 'Maintenance mode is now on.' : 'Maintenance settings saved. The full website is live.');
        Response::redirect('/admin/maintenance');
    }
}
