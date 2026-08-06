<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;
use App\Services\FlowerMatcher;

final class FloristController
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings, private readonly FlowerMatcher $matcher) {}

    public function index(Request $request): void
    {
        $flower = (int) $request->query('flower', 0);
        View::render('pages/florist', [
            'title' => 'Florist inspiration', 'settings' => $this->settings->all(),
            'samples' => $this->catalogue->samples(['flower' => $flower]),
            'flowers' => $this->catalogue->flowers(), 'activeFlower' => $flower,
        ], 'layouts/public');
    }

    public function show(Request $request): void
    {
        $sample = $this->catalogue->sampleBySlug((string) $request->param('slug'));
        if (!$sample) {
            http_response_code(404);
            View::render('pages/not-found', [], 'layouts/public');
            return;
        }
        View::render('pages/florist-detail', ['title' => $sample['name'], 'settings' => $this->settings->all(), 'sample' => $sample], 'layouts/public');
    }

    public function matches(Request $request): void
    {
        $flowers = $request->query('flowers', []);
        if (is_string($flowers)) $flowers = array_filter(explode(',', $flowers));
        $colours = $request->query('colours', []);
        if (is_string($colours)) $colours = array_filter(explode(',', $colours));
        $ranked = $this->matcher->rank($this->catalogue->samples(), (array) $flowers, [
            'colour_ids' => (array) $colours,
            'arrangement_type_id' => (int) $request->query('arrangement', 0),
            'occasion_id' => (int) $request->query('occasion', 0),
        ]);
        Response::json(['ok' => true, 'data' => array_slice($ranked, 0, 12)]);
    }
}

