<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AuditRepository;
use App\Repositories\SettingsRepository;

final class SettingsController
{
    public function __construct(private readonly SettingsRepository $settings, private readonly AuditRepository $audit) {}
    public function edit(Request $request): void { View::render('admin/settings', ['title' => 'Shop settings', 'settings' => $this->settings->all(false)], 'layouts/admin'); }
    public function update(Request $request): void
    {
        $before = $this->settings->all(false); $user = Auth::user();
        $input = $request->all();
        $input['allow_combined_enquiries'] = $request->input('allow_combined_enquiries') ? '1' : '0';
        foreach (['business_hours','social_links'] as $jsonKey) {
            if (isset($input[$jsonKey]) && is_string($input[$jsonKey])) $input[$jsonKey] = json_decode($input[$jsonKey], true) ?: [];
        }
        $this->settings->save($input, (int) $user['id']);
        $this->audit->record((int) $user['id'], 'update', 'shop_settings', null, $before, $this->settings->all(false), $request->ip());
        Session::flash('success', 'Shop settings saved.'); Response::redirect('/admin/settings');
    }
}

