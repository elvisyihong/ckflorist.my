<?php

declare(strict_types=1);

test('brand asset URLs accept only local public media paths', function (): void {
    assert_same('/public/uploads/2026/08/logo.webp', brand_asset_url(['logo' => 'public/uploads/2026/08/logo.webp']));
    assert_same('/public/assets/images/logo.png', brand_asset_url(['logo' => '/public/assets/images/logo.png']));
    assert_same('', brand_asset_url(['logo' => 'javascript:alert(1)']));
    assert_same('', brand_asset_url(['logo' => 'https://example.com/logo.png']));
});

test('brand mark renders the configured logo and escapes its label', function (): void {
    $mark = brand_mark(['logo' => '/public/uploads/logo.webp'], 'CK Florist <Admin>');
    assert_true(str_contains($mark, 'class="brand-logo"'));
    assert_true(str_contains($mark, 'CK Florist &lt;Admin&gt;'));
});

test('favicon falls back to the configured logo', function (): void {
    assert_same('/public/uploads/logo.webp', brand_favicon(['logo' => '/public/uploads/logo.webp', 'favicon' => '']));
});
