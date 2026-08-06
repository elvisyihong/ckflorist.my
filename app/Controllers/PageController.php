<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;

final class PageController
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings) {}
    public function gallery(Request $request): void { View::render('pages/gallery', ['title' => 'Gallery', 'settings' => $this->settings->all(), 'items' => $this->catalogue->gallery()], 'layouts/public'); }
    public function about(Request $request): void { View::render('pages/about', ['title' => 'Our story', 'settings' => $this->settings->all()], 'layouts/public'); }
    public function contact(Request $request): void { View::render('pages/contact', ['title' => 'Contact CK Florist', 'settings' => $this->settings->all()], 'layouts/public'); }
    public function policy(Request $request): void
    {
        $slug = (string) $request->param('slug');
        $fallback = ['terms' => 'Submitting an enquiry does not confirm an order.', 'privacy' => 'We use submitted information only to respond and coordinate fulfilment.', 'cancellation' => 'Contact us as soon as possible to discuss changes or cancellation.'];
        $policy = null;
        if (Database::available()) {
            $statement = Database::connection()->prepare('SELECT title, body FROM policies WHERE slug = :slug AND is_active = 1 LIMIT 1');
            $statement->execute(['slug' => $slug]);
            $policy = $statement->fetch();
        }
        $policy = is_array($policy) ? $policy : ['title' => ucfirst($slug) . ' policy', 'body' => $fallback[$slug] ?? 'This policy is being prepared.'];
        View::render('pages/policy', ['title' => $policy['title'], 'settings' => $this->settings->all(), 'policy' => $policy], 'layouts/public');
    }
}

