<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use Throwable;

final class Application
{
    private Router $router;
    private array $instances = [];

    public function __construct(private readonly array $config)
    {
        $this->router = new Router($this);
        $this->instances[self::class] = $this;
        $this->instances[Router::class] = $this->router;
    }

    public function boot(): void
    {
        SecurityHeaders::send();
        Session::start($this->config['session']);
        Csrf::ensureToken();
        $registerRoutes = require BASE_PATH . '/config/routes.php';
        $registerRoutes($this->router);
    }

    public function run(): void
    {
        try {
            $this->router->dispatch(Request::capture());
        } catch (Throwable $exception) {
            $requestId = bin2hex(random_bytes(6));
            error_log(sprintf('[%s] %s in %s:%d', $requestId, $exception->getMessage(), $exception->getFile(), $exception->getLine()));
            http_response_code(500);
            if (Env::bool('APP_DEBUG')) {
                echo '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
                return;
            }
            View::render('pages/error', ['requestId' => $requestId], 'layouts/public');
        }
    }

    public function make(string $class): object
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $this->instances[$class] = new $class();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new \RuntimeException("Cannot resolve {$class}::\${$parameter->getName()}");
            }
            $arguments[] = $this->make($type->getName());
        }

        return $this->instances[$class] = $reflection->newInstanceArgs($arguments);
    }

    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        $value = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
