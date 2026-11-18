<?php

declare(strict_types=1);

namespace app\controller\user;

final class User
{
    public function read(int $id): array
    {
        $think = db();

        $rows = (array) $think->query(
            'SELECT id, username, cid, create_time, update_time
             FROM user WHERE id = ?',
            [$id]
        );

        return ['user' => $rows[0] ?? null];
    }
}
