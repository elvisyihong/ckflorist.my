<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $routeParams = [];
    private ?array $json = null;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
        private readonly array $files,
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }
        $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        return new self($method, '/' . trim($path, '/') ?: '/', $_GET, $_POST, $_SERVER, $_FILES);
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function file(string $key): ?array { return isset($this->files[$key]) && is_array($this->files[$key]) ? $this->files[$key] : null; }
    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->server[$key]) ? (string) $this->server[$key] : $default;
    }
    public function ip(): string { return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0'); }
    public function isJson(): bool { return str_contains((string) $this->header('Content-Type', ''), 'application/json'); }

    public function input(string $key, mixed $default = null): mixed
    {
        $source = $this->isJson() ? $this->json() : $this->post;
        return $source[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->isJson() ? $this->json() : $this->post;
    }

    public function setRouteParams(array $params): void { $this->routeParams = $params; }
    public function param(string $key, mixed $default = null): mixed { return $this->routeParams[$key] ?? $default; }

    private function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }
        $decoded = json_decode((string) file_get_contents('php://input'), true);
        return $this->json = is_array($decoded) ? $decoded : [];
    }
}
