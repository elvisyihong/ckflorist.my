<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;

final class CafeController
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings) {}
    public function index(Request $request): void
    {
        View::render('pages/cafe', ['title' => 'Café menu', 'settings' => $this->settings->all(), 'categories' => $this->catalogue->cafeCategories(), 'products' => $this->catalogue->cafeProducts()], 'layouts/public');
    }
}

