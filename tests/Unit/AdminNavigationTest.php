<?php

declare(strict_types=1);

test('admin layout includes an accessible mobile navigation toggle', function (): void {
    $layout = file_get_contents(BASE_PATH . '/views/layouts/admin.php');
    assert_true(is_string($layout));

    foreach (['data-admin-nav-toggle', 'aria-controls="admin-navigation"', 'data-admin-navigation', 'data-admin-nav-close', 'data-admin-nav-backdrop'] as $hook) {
        assert_true(str_contains($layout, $hook), "Missing admin navigation hook: {$hook}");
    }
});
