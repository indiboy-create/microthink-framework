<?php

declare(strict_types=1);

namespace app\controller;

use mini\View;

final class Index
{
    public function __construct(private View $view)
    {
    }

    public function index(): array
    {
        return [
            'framework' => 'microthink-framework',
            'database' => 'sqlite',
            'tables' => [
                'user_class',
                'user',
                'category',
                'article',
                'article_data_1',
                'public',
                'menu_class',
                'menu',
                'template_class',
                'template',
                'website',
            ],
        ];
    }

    public function page(): string
    {
        $think = db();

        $articles = (array) $think->query(
            'SELECT id, title, newstime FROM article ORDER BY id DESC LIMIT 20'
        );

        return $this->view->render('index', [
            'articles' => $articles,
        ]);
    }
}
