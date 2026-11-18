<?php

declare(strict_types=1);

use mini\App;
use mini\Db;
use mini\Request;

function app(): App
{
    return App::instance();
}

function config(?string $key = null, mixed $default = null): mixed
{
    return app()->config($key, $default);
}

function request(): Request
{
    return app()->request();
}

function db(): Db
{
    return app()->db();
}

function view(string $template, array $vars = []): string
{
    return app()->view()->render($template, $vars);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function loop(string $sql, array $bindings = [], string $template = ''): string
{
    $rows = (array) db()->query($sql, $bindings);
    $total = count($rows);
    $html = '';

    foreach ($rows as $index => $row) {
        $row = is_array($row) ? $row : [];

        $vars = $row + [
            'i' => $index + 1,
            'index' => $index,
            'total' => $total,
        ];

        $html .= preg_replace_callback(
            '/\[!--(!?)([A-Za-z_][A-Za-z0-9_]*)--\]/',
            static function (array $matches) use ($vars): string {
                $name = $matches[2];
                $value = (string) ($vars[$name] ?? '');

                return $matches[1] === '' ? e($value) : $value;
            },
            $template
        ) ?? '';
    }

    return $html;
}
