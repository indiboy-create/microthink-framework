<?php

declare(strict_types=1);

namespace app\controller\article;

use mini\Request;
use mini\Response;
use Throwable;

final class Article
{
    public function index(): array
    {
        $think = db();

        return [
            'articles' => (array) $think->query(
                'SELECT id, user_id, cid, title, titleurl, smalltext, newstime
                 FROM article
                 ORDER BY id DESC
                 LIMIT 100'
            ),
        ];
    }

    public function read(int $id): array
    {
        $think = db();

        $rows = (array) $think->query(
            'SELECT a.*, d.newstext AS content
             FROM article a
             LEFT JOIN article_data_1 d ON d.id = a.id
             WHERE a.id = ?',
            [$id]
        );

        return ['article' => $rows[0] ?? null];
    }

    public function create(Request $request): array|Response
    {
        $writeToken = (string) config('security.write_token', '');
        $provided = (string) $request->header('X-Write-Token', '');

        if ($writeToken === '' || $provided === '' || !hash_equals($writeToken, $provided)) {
            return Response::json(['error' => 'Forbidden'], 403);
        }

        $think = db();

        $userId = (int) config('security.default_user_id', 1);
        $categoryId = (int) $request->input('cid', 1);
        $title = trim((string) $request->input('title', ''));
        $content = (string) $request->input('content', '');

        if ($title === '' || mb_strlen($title) > 200) {
            return Response::json(['error' => '标题不能为空且不能超过 200 个字符'], 422);
        }

        if ($content === '') {
            return Response::json(['error' => '正文不能为空'], 422);
        }

        if ($categoryId <= 0) {
            return Response::json(['error' => '无效的分类'], 422);
        }

        $category = (array) $think->query(
            'SELECT id FROM category WHERE id = ? AND status = 1',
            [$categoryId]
        );

        if ($category === []) {
            return Response::json(['error' => '分类不存在或未启用'], 422);
        }

        $now = time();

        $think->beginTransaction();

        try {
            $id = (int) $think->query(
                'INSERT INTO article
                 (user_id, cid, title, smalltext, status, checked, havehtml, truetime, lastdotime, newstime)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $categoryId,
                    $title,
                    mb_substr($content, 0, 255),
                    1,
                    1,
                    0,
                    $now,
                    $now,
                    $now,
                ]
            );

            $think->query(
                'INSERT INTO article_data_1
                 (id, cid, newstext, writer, befrom, infotags)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$id, $categoryId, $content, 'admin', '', '']
            );

            $think->query(
                'UPDATE article SET titleurl = ? WHERE id = ?',
                ['/articles/' . $id, $id]
            );

            $think->commit();
        } catch (Throwable $e) {
            $think->rollBack();

            $data = ['error' => '创建文章失败'];

            if (config('debug', false)) {
                $data['message'] = $e->getMessage();
            }

            return Response::json($data, 500);
        }

        return ['created' => true, 'id' => $id];
    }
}
