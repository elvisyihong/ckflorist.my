<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;
use App\Services\SelectionService;

final class BuilderController
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings, private readonly SelectionService $selection) {}

    public function index(Request $request): void
    {
        View::render('pages/builder', [
            'title' => 'Customise your bouquet', 'settings' => $this->settings->all(),
            'draft' => $this->selection->current()['bouquet'] ?? [],
            'occasions' => $this->catalogue->occasions(), 'flowers' => $this->catalogue->flowers(),
            'samples' => $this->catalogue->samples(), 'colours' => $this->catalogue->colours(),
            'sizes' => $this->catalogue->sizes(), 'wrappings' => $this->catalogue->wrappings(),
            'decorations' => $this->catalogue->decorations(),
        ], 'layouts/public');
    }
}

