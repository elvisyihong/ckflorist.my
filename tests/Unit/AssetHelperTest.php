<?php

declare(strict_types=1);

test('asset URLs include a file version to invalidate immutable browser caches', function (): void {
    $url = asset('js/app.js');
    assert_true(str_starts_with($url, '/public/assets/js/app.js?v='));
    assert_true((bool) preg_match('/\?v=[0-9]+$/', $url));
});

test('missing asset URLs remain stable without a fabricated version', function (): void {
    assert_same('/public/assets/images/does-not-exist.webp', asset('/images/does-not-exist.webp'));
});
