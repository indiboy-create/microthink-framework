<?php

declare(strict_types=1);

return [
    'debug' => false,

    'db' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/app.sqlite',

        // MySQL:
        // 'driver' => 'mysql',
        // 'host' => '127.0.0.1',
        // 'port' => 3306,
        // 'database' => 'microthink',
        // 'username' => 'root',
        // 'password' => '',
        // 'charset' => 'utf8mb4',
    ],

    'view' => [
        'path' => __DIR__ . '/../app/view',
    ],

    'thumb' => [
        'source' => __DIR__ . '/../public/uploads',
        'cache' => __DIR__ . '/../public/uploads/.cache',
        'max_image_size' => 2000,
        'max_output_size' => 2000,
        'defaults' => [
            'q' => 80,
        ],
        'presets' => [
            'small' => ['w' => 120, 'h' => 120, 'fit' => 'crop'],
            'medium' => ['w' => 320, 'h' => 320, 'fit' => 'crop'],
            'large' => ['w' => 800, 'h' => 800, 'fit' => 'contain'],
        ],
    ],

    'install' => [
        'require_lock' => true,
        'allowed_ips' => ['127.0.0.1', '::1'],
    ],

    'security' => [
        'write_token' => getenv('MICROTHINK_WRITE_TOKEN') ?: '',
        'default_user_id' => 1,
    ],

    'middleware' => [],
];
