<?php

declare(strict_types=1);

namespace mini;

final class Router
{
    public function __construct(private array $routes)
    {
    }

    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $rule => $handler) {
            $parts = explode(' ', trim((string) $rule), 2);

            if (count($parts) !== 2) {
                continue;
            }

            [$routeMethod, $routePath] = $parts;

            if (strtoupper($routeMethod) !== strtoupper($method)) {
                continue;
            }

            if (preg_match($this->compile($routePath), $path, $matches) !== 1) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn (string|int $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            return [$handler, $params];
        }

        return null;
    }

    public function hasPath(string $path): bool
    {
        foreach ($this->routes as $rule => $_) {
            $parts = explode(' ', trim((string) $rule), 2);

            if (count($parts) !== 2) {
                continue;
            }

            if (preg_match($this->compile($parts[1]), $path) === 1) {
                return true;
            }
        }

        return false;
    }

    private function compile(string $path): string
    {
        $path = '/' . trim($path, '/');

        if ($path === '/') {
            return '#^/$#';
        }

        $segments = explode('/', trim($path, '/'));
        $parts = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\*\}$/', $segment, $matches) === 1) {
                $parts[] = '(?P<' . $matches[1] . '>.+)';
            } elseif (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $matches) === 1) {
                $parts[] = '(?P<' . $matches[1] . '>[^/]+)';
            } else {
                $parts[] = preg_quote($segment, '#');
            }
        }

        return '#^/' . implode('/', $parts) . '/?$#';
    }
}
