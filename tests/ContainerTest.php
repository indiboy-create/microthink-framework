<?php

declare(strict_types=1);

namespace mini\Tests;

use mini\Container;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContainerTest extends TestCase
{
    public function testOptionalClassDependencyUsesDefault(): void
    {
        $service = (new Container())->make(OptionalDependencyService::class);

        self::assertNull($service->dependency);
    }

    public function testMethodInjectsGenericClass(): void
    {
        $handler = new class {
            public function handle(stdClass $dependency): string
            {
                return $dependency::class;
            }
        };

        self::assertSame(
            stdClass::class,
            (new Container())->call([$handler, 'handle'])
        );
    }
}

class OptionalDependencyService
{
    public function __construct(public ?stdClass $dependency = null)
    {
    }
}
