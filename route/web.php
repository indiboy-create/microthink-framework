<?php

declare(strict_types=1);

use app\controller\article\Article;
use app\controller\article\Category;
use app\controller\Index;
use app\controller\install\Install;
use app\controller\Thumbnail;
use app\controller\user\User;

return [
    'GET /install' => [Install::class, 'index'],
    'POST /install' => [Install::class, 'run'],
    'GET /install/complete' => [Install::class, 'complete'],

    'GET /' => [Index::class, 'index'],
    'GET /page' => [Index::class, 'page'],

    'GET /thumb/{path*}' => [Thumbnail::class, 'show'],

    'GET /users/{id}' => [User::class, 'read'],

    'GET /articles' => [Article::class, 'index'],
    'GET /articles/{id}' => [Article::class, 'read'],
    'POST /articles' => [Article::class, 'create'],

    'GET /categories' => [Category::class, 'index'],
];
