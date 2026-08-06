<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly Application $application) {}

    public function get(string $path, array|callable $handler, array $options = []): void { $this->add('GET', $path, $handler, $options); }
    public function post(string $path, array|callable $handler, array $options = []): void { $this->add('POST', $path, $handler, $options); }
    public function delete(string $path, array|callable $handler, array $options = []): void { $this->add('DELETE', $path, $handler, $options); }

    public function add(string $method, string $path, array|callable $handler, array $options = []): void
    {
        $normalized = '/' . trim($path, '/');
        $normalized = $normalized === '/' ? '/' : rtrim($normalized, '/');
        $pattern = preg_replace_callback('/\{([A-Za-z_][A-Za-z0-9_]*)(?::([^}]+))?\}/', static function (array $match): string {
            return '(?P<' . $match[1] . '>' . ($match[2] ?? '[^/]+') . ')';
        }, $normalized);
        $this->routes[] = compact('method', 'path', 'pattern', 'handler', 'options');
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            if (!preg_match('#^' . $route['pattern'] . '$#u', $request->path(), $matches)) {
                continue;
            }
            $request->setRouteParams(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            $this->enforce($route['options'], $request);
            $this->invoke($route['handler'], $request);
            return;
        }

        http_response_code(404);
        View::render('pages/not-found', [], 'layouts/public');
    }

    private function enforce(array $options, Request $request): void
    {
        if ($request->method() !== 'GET' && ($options['csrf'] ?? true)) {
            $token = (string) ($request->input('_token') ?: $request->header('X-CSRF-Token', ''));
            if (!Csrf::validate($token)) {
                if ($request->isJson()) {
                    Response::json(['ok' => false, 'error' => 'Your session expired. Refresh and try again.'], 419);
                }
                Session::flash('error', 'Your session expired. Please try again.');
                Response::redirect($request->header('Referer', '/') ?: '/');
            }
        }
        if (isset($options['permission'])) {
            Auth::requirePermission((string) $options['permission']);
        }
    }

    private function invoke(array|callable $handler, Request $request): void
    {
        if (is_callable($handler)) {
            $handler($request);
            return;
        }
        [$class, $method] = $handler;
        $controller = $this->application->make($class);
        $controller->{$method}($request);
    }
}

