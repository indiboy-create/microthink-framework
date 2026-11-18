<?php

declare(strict_types=1);

namespace mini\Tests;

use mini\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testHeaderReadsStandardServerKeys(): void
    {
        $request = new Request([], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TEST' => 'yes',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        self::assertSame('application/json', $request->header('Content-Type'));
        self::assertSame('yes', $request->header('X-Test'));
        self::assertSame('127.0.0.1', $request->ip());
    }
}
