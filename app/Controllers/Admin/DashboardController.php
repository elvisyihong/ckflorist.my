<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Repositories\EnquiryRepository;

final class DashboardController
{
    public function __construct(private readonly EnquiryRepository $enquiries) {}
    public function index(Request $request): void
    {
        $counts = Database::available() ? $this->enquiries->dashboardCounts() : [];
        $recent = Database::available() ? $this->enquiries->recent() : [];
        View::render('admin/dashboard', ['title' => 'Dashboard', 'counts' => $counts, 'recent' => $recent], 'layouts/admin');
    }
}

