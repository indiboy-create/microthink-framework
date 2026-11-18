<?php

declare(strict_types=1);

namespace mini\Tests;

use mini\View;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/microthink-view-test-' . bin2hex(random_bytes(6));

        if (!mkdir($this->dir, 0700, true) && !is_dir($this->dir)) {
            throw new \RuntimeException('无法创建测试模板目录');
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->dir);
    }

    public function testVariablesAreEscaped(): void
    {
        file_put_contents($this->dir . '/hello.php', '<?= e($name) ?>');

        self::assertSame(
            '&lt;b&gt;',
            (new View($this->dir))->render('hello', ['name' => '<b>'])
        );
    }
}
