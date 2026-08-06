<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $viewPath = BASE_PATH . '/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        if ($layout === null) {
            echo $content;
            return;
        }
        $layoutPath = BASE_PATH . '/views/' . $layout . '.php';
        require $layoutPath;
    }
}

