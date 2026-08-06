<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $url = '/public/assets/' . $path;
    $file = BASE_PATH . $url;
    if (!is_file($file)) {
        return $url;
    }

    static $versions = [];
    $versions[$file] ??= (string) filemtime($file);
    return $url . '?v=' . rawurlencode($versions[$file]);
}

function app_url(string $path = '/'): string
{
    return rtrim(Env::get('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function money(float|int|string|null $amount, string $currency = 'BND'): string
{
    return $amount === null ? 'Price on request' : $currency . ' ' . number_format((float) $amount, 2);
}

function selected_count(): int
{
    $selection = Session::get('selection', []);
    return (isset($selection['bouquet']) ? 1 : 0) + count($selection['cafe'] ?? []);
}

function flash(string $key): mixed
{
    return Session::pullFlash($key);
}

function app_config(string $key, mixed $default = null): mixed
{
    static $configuration = null;
    $configuration ??= require BASE_PATH . '/config/app.php';
    $value = $configuration;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) return $default;
        $value = $value[$segment];
    }
    return $value;
}
