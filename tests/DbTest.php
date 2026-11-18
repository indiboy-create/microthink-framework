<?php

declare(strict_types=1);

namespace mini\Tests;

use mini\Db;
use PHPUnit\Framework\TestCase;

final class DbTest extends TestCase
{
    private Db $db;

    protected function setUp(): void
    {
        $this->db = new Db([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->db->query('CREATE TABLE item (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
    }

    public function testResultSetIsDetectedByColumns(): void
    {
        $rows = $this->db->query(
            "/* leading comment */ WITH x AS (SELECT 1 AS id, 'cte' AS name) SELECT * FROM x"
        );

        self::assertCount(1, $rows);
        self::assertSame('cte', $rows[0]['name']);
    }

    public function testTransactionRollback(): void
    {
        $this->db->beginTransaction();
        $this->db->query('INSERT INTO item (name) VALUES (?)', ['temporary']);
        $this->db->rollBack();

        self::assertCount(0, $this->db->query('SELECT * FROM item'));
    }
}
