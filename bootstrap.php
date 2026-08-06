<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Env;

define('BASE_PATH', __DIR__);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require BASE_PATH . '/app/Support/helpers.php';

Env::load(BASE_PATH . '/.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Brunei'));

$isProduction = Env::get('APP_ENV', 'production') === 'production';
ini_set('display_errors', $isProduction ? '0' : '1');
error_reporting(E_ALL);

$application = new Application(require BASE_PATH . '/config/app.php');
$application->boot();

return $application;

