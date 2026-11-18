<?php

declare(strict_types=1);

namespace app\controller;

use League\Glide\Filesystem\FileNotFoundException as GlideFileNotFoundException;
use League\Glide\ServerFactory;
use mini\Request;
use mini\Response;
use Throwable;

final class Thumbnail
{
    private const ALLOWED_PARAMS = [
        'w',
        'h',
        'fit',
        'dpr',
        'crop',
        'q',
        'fm',
        'p',
        'bri',
        'con',
        'gam',
        'sharp',
        'filt',
        'flip',
        'blur',
        'pixel',
        'bg',
        'border',
        'or',
    ];

    public function show(Request $request, string $path): Response
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return Response::json(['error' => 'Invalid or missing image path'], 400);
        }

        $source = (string) config('thumb.source', dirname(__DIR__, 2) . '/public/uploads');

        if (!is_file(rtrim($source, '/\\') . DIRECTORY_SEPARATOR . $path)) {
            return Response::json(['error' => 'Image not found'], 404);
        }

        try {
            $server = ServerFactory::create([
                'source' => $source,
                'cache' => (string) config('thumb.cache', dirname(__DIR__, 2) . '/public/uploads/.cache'),
                'max_image_size' => (int) config('thumb.max_image_size', 2000),
                'defaults' => (array) config('thumb.defaults', []),
                'presets' => (array) config('thumb.presets', []),
                'cache_with_file_extensions' => true,
            ]);

            $cachedPath = $server->makeImage($path, $this->params($request));
            $binary = $server->getCache()->read($cachedPath);
            $mime = $server->getCache()->mimeType($cachedPath);
        } catch (GlideFileNotFoundException) {
            return Response::json(['error' => 'Image not found'], 404);
        } catch (Throwable $e) {
            $data = ['error' => 'Unable to generate thumbnail'];

            if (config('debug', false)) {
                $data['message'] = $e->getMessage();
            }

            return Response::json($data, 500);
        }

        return new Response($binary, 200, [
            'Content-Type' => $mime ?: 'application/octet-stream',
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function normalizePath(string $path): string
    {
        $path = rawurldecode($path);
        $path = str_replace('\\', '/', $path);
        $path = trim($path, '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return '';
        }

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return trim($path, '/');
    }

    private function params(Request $request): array
    {
        $maxOutput = max(1, min(5000, (int) config('thumb.max_output_size', 2000)));
        $limits = [
            'w' => [1, $maxOutput],
            'h' => [1, $maxOutput],
            'dpr' => [1, 4],
            'q' => [1, 100],
            'bri' => [-100, 100],
            'con' => [-100, 100],
            'gam' => [1, 10],
            'sharp' => [0, 100],
            'blur' => [0, 100],
            'pixel' => [1, 100],
        ];
        $integerKeys = ['w', 'h', 'dpr', 'q', 'sharp', 'blur', 'pixel'];
        $params = [];

        foreach (self::ALLOWED_PARAMS as $key) {
            $value = $request->query($key);

            if (!is_scalar($value) || $value === '') {
                continue;
            }

            $value = (string) $value;

            if (isset($limits[$key])) {
                if (!is_numeric($value)) {
                    continue;
                }

                [$min, $max] = $limits[$key];
                $number = max($min, min($max, (float) $value));
                $params[$key] = in_array($key, $integerKeys, true)
                    ? (int) round($number)
                    : $number;

                continue;
            }

            $params[$key] = substr(trim($value), 0, 64);
        }

        return $params;
    }
}
