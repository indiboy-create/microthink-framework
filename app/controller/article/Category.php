<?php

declare(strict_types=1);

namespace app\controller\article;

final class Category
{
    public function index(): array
    {
        $think = db();

        return [
            'categories' => (array) $think->query(
                'SELECT * FROM category ORDER BY id ASC'
            ),
        ];
    }
}
