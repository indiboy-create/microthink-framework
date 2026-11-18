<?php

declare(strict_types=1);

namespace mini\Tests;

use mini\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ResponseTest extends TestCase
{
    public function testRedirectSetsLocationAndStatus(): void
    {
        $response = Response::redirect('/install', 302);
        $reflection = new ReflectionClass($response);

        $status = $reflection->getProperty('status');
        $status->setAccessible(true);

        $headers = $reflection->getProperty('headers');
        $headers->setAccessible(true);

        self::assertSame(302, $status->getValue($response));
        self::assertSame(['Location' => '/install'], $headers->getValue($response));
    }
}
