<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;

final class HomeController
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings) {}

    public function index(Request $request): void
    {
        View::render('pages/home', [
            'title' => 'Florist craft, café warmth',
            'settings' => $this->settings->all(),
            'samples' => array_slice($this->catalogue->samples(), 0, 2),
            'cafeProducts' => array_slice($this->catalogue->cafeProducts(), 0, 2),
        ], 'layouts/public');
    }
}

