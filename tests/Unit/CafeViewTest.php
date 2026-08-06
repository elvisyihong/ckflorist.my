<?php

declare(strict_types=1);

test('cafe menu includes the mobile discovery controls', function (): void {
    $view = file_get_contents(BASE_PATH . '/views/pages/cafe.php');
    assert_true(is_string($view));

    foreach (['data-cafe-search', 'data-offer-track', 'data-cafe-category', 'data-cafe-grid', 'data-product-total-price'] as $hook) {
        assert_true(str_contains($view, $hook), "Missing café interface hook: {$hook}");
    }
});

test('cafe cards keep product information concise', function (): void {
    $card = file_get_contents(BASE_PATH . '/views/components/cafe-card.php');
    assert_true(is_string($card));
    assert_true(str_contains($card, 'cafe-card-image'));
    assert_true(str_contains($card, 'cafe-card-body'));
    assert_true(!str_contains($card, '<p><?= e($product[\'description\']) ?>'));
});
