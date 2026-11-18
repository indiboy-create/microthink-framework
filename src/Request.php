<?php

declare(strict_types=1);

namespace mini;

final class Request
{
    private ?array $json = null;

    public function __construct(
        private array $query = [],
        private array $post = [],
        private array $server = []
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        return '/' . trim($path, '/');
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }

        return $this->post[$key] ?? $default;
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->json === null) {
            $raw = file_get_contents('php://input');
            $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            $this->json = is_array($decoded) ? $decoded : [];
        }

        if ($key === null) {
            return $this->json;
        }

        return $this->json[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key]
            ?? $this->query[$key]
            ?? ($this->json()[$key] ?? $default);
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $normalized = strtoupper(str_replace('-', '_', $name));

        return $this->server['HTTP_' . $normalized]
            ?? $this->server[$normalized]
            ?? $this->server[$name]
            ?? $default;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '');
    }
}
