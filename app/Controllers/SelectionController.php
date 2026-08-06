<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\SettingsRepository;
use App\Services\SelectionService;

final class SelectionController
{
    public function __construct(private readonly SelectionService $selection, private readonly SettingsRepository $settings) {}

    public function index(Request $request): void
    {
        View::render('pages/selection', ['title' => 'Your selection', 'settings' => $this->settings->all(), 'selection' => $this->selection->current()], 'layouts/public');
    }
    public function current(Request $request): void { Response::json(['ok' => true, 'data' => $this->selection->current()]); }
    public function bouquet(Request $request): void { Response::json(['ok' => true, 'data' => $this->selection->saveBouquet($request->all())]); }
    public function cafe(Request $request): void
    {
        try { Response::json(['ok' => true, 'data' => $this->selection->addCafe($request->all())], 201); }
        catch (\InvalidArgumentException $exception) { Response::json(['ok' => false, 'error' => $exception->getMessage()], 422); }
    }
    public function removeCafe(Request $request): void { Response::json(['ok' => true, 'data' => $this->selection->removeCafe((string) $request->param('key'))]); }
}

