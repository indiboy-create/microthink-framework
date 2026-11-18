<?php

declare(strict_types=1);

namespace mini;

final class Response
{
    public function __construct(
        private string $content = '',
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return new self($json === false ? 'null' : $json, $status, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, [
            'Location' => $url,
        ]);
    }

    public static function make(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value) || is_int($value) || is_bool($value) || $value === null) {
            return self::json($value);
        }

        return self::html((string) $value);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
