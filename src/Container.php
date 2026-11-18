<?php

declare(strict_types=1);

namespace mini;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use RuntimeException;

final class Container
{
    private array $instances = [];

    public function singleton(string $class, object $instance): void
    {
        $this->instances[$class] = $instance;
    }

    public function make(string $class): object
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->make($type->getName());
                continue;
            }

            throw new RuntimeException('无法解析构造参数：' . $class . '::$' . $parameter->getName());
        }

        return $reflection->newInstanceArgs($arguments);
    }

    public function call(callable $callable, array $params = []): mixed
    {
        $reflection = $callable instanceof Closure
            ? new ReflectionFunction($callable)
            : new ReflectionMethod($callable[0], $callable[1]);

        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $params)) {
                $arguments[] = $this->cast($params[$name], $type);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->make($type->getName());
                continue;
            }

            throw new RuntimeException('无法解析方法参数：' . $name);
        }

        return $callable(...$arguments);
    }

    private function cast(mixed $value, ?ReflectionType $type): mixed
    {
        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $value,
            default => $value,
        };
    }
}
