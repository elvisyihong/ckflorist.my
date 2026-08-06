<?php

declare(strict_types=1);

use App\Services\MaintenanceMode;

test('maintenance mode is disabled when its setting is absent', function (): void {
    assert_true(!(new MaintenanceMode())->isEnabled([]));
});

test('maintenance mode intercepts public routes when enabled', function (): void {
    $mode = new MaintenanceMode();
    assert_true($mode->shouldIntercept('/', ['maintenance_mode' => true]));
    assert_true($mode->shouldIntercept('/api/selection', ['maintenance_mode' => '1']));
});

test('maintenance mode always leaves admin routes available', function (): void {
    $mode = new MaintenanceMode();
    assert_true(!$mode->shouldIntercept('/admin', ['maintenance_mode' => true]));
    assert_true(!$mode->shouldIntercept('/admin/login', ['maintenance_mode' => true]));
    assert_true($mode->shouldIntercept('/administrator', ['maintenance_mode' => true]));
});

test('authenticated administrators bypass maintenance on public routes', function (): void {
    $mode = new MaintenanceMode();
    assert_true(!$mode->shouldIntercept('/', ['maintenance_mode' => true], true));
    assert_true(!$mode->shouldIntercept('/florist', ['maintenance_mode' => '1'], true));
});

test('maintenance menu image ids are positive unique integers in display order', function (): void {
    $ids = (new MaintenanceMode())->imageIds(['maintenance_menu_images' => [4, '2', 4, 0, -3, 'not-an-id', 8]]);
    assert_same([4, 2, 8], $ids);
});
