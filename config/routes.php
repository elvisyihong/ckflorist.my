<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Controllers\Admin\ResourceController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\UploadController;
use App\Controllers\BuilderController;
use App\Controllers\CafeController;
use App\Controllers\EnquiryController;
use App\Controllers\FloristController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\SelectionController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/florist', [FloristController::class, 'index']);
    $router->get('/florist/{slug}', [FloristController::class, 'show']);
    $router->get('/customise', [BuilderController::class, 'index']);
    $router->get('/cafe', [CafeController::class, 'index']);
    $router->get('/gallery', [PageController::class, 'gallery']);
    $router->get('/about', [PageController::class, 'about']);
    $router->get('/contact', [PageController::class, 'contact']);
    $router->get('/selection', [SelectionController::class, 'index']);
    $router->get('/enquiry/{reference:CKF-[0-9]+-[A-F0-9]+}', [EnquiryController::class, 'show']);
    $router->get('/policies/{slug}', [PageController::class, 'policy']);

    $router->get('/api/florist/matches', [FloristController::class, 'matches']);
    $router->get('/api/selection', [SelectionController::class, 'current']);
    $router->post('/api/selection/bouquet', [SelectionController::class, 'bouquet']);
    $router->post('/api/selection/cafe', [SelectionController::class, 'cafe']);
    $router->delete('/api/selection/cafe/{key:[a-f0-9]+}', [SelectionController::class, 'removeCafe']);
    $router->post('/enquiries', [EnquiryController::class, 'store']);

    $router->get('/admin/login', [AdminAuthController::class, 'form']);
    $router->post('/admin/login', [AdminAuthController::class, 'login']);
    $router->post('/admin/logout', [AdminAuthController::class, 'logout'], ['permission' => 'admin.view']);
    $router->get('/admin', [DashboardController::class, 'index'], ['permission' => 'admin.view']);
    $router->get('/admin/settings', [SettingsController::class, 'edit'], ['permission' => 'settings.manage']);
    $router->post('/admin/settings', [SettingsController::class, 'update'], ['permission' => 'settings.manage']);
    $router->post('/admin/uploads', [UploadController::class, 'store'], ['permission' => 'catalogue.manage']);
    $router->get('/admin/enquiries', [AdminEnquiryController::class, 'index'], ['permission' => 'enquiries.manage']);
    $router->get('/admin/enquiries/{id:[0-9]+}', [AdminEnquiryController::class, 'show'], ['permission' => 'enquiries.manage']);
    $router->post('/admin/enquiries/{id:[0-9]+}/status', [AdminEnquiryController::class, 'status'], ['permission' => 'enquiries.manage']);
    $router->get('/admin/{resource}/create', [ResourceController::class, 'create'], ['permission' => 'catalogue.manage']);
    $router->get('/admin/{resource}/{id:[0-9]+}/edit', [ResourceController::class, 'edit'], ['permission' => 'catalogue.manage']);
    $router->post('/admin/{resource}/{id:[0-9]+}/delete', [ResourceController::class, 'delete'], ['permission' => 'catalogue.manage']);
    $router->post('/admin/{resource}/{id:[0-9]+}', [ResourceController::class, 'update'], ['permission' => 'catalogue.manage']);
    $router->post('/admin/{resource}', [ResourceController::class, 'store'], ['permission' => 'catalogue.manage']);
    $router->get('/admin/{resource}', [ResourceController::class, 'index'], ['permission' => 'catalogue.manage']);
};
