<?php

declare(strict_types=1);

namespace Tests;

use ErrorException;
use ReflectionClass;
use ReflectionException;

/**
 * for private, protected method, property access
 * @template T of object
 */
readonly class FullAccessWrapper
{
    /** @var ReflectionClass<T> $reflection */
    private ReflectionClass $reflection;

    /** @var T $targetInstance */
    private object $targetInstance;

    /**
     * @param T $targetInstance
     */
    public function __construct(object $targetInstance)
    {
        $this->targetInstance = $targetInstance;
        $this->reflection = new ReflectionClass($targetInstance);
    }

    /**
     * @param array<int,mixed> $args
     * @throws ReflectionException
     */
    public function __call(string $methodName, array $args): mixed
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->targetInstance, $args);
    }

    /**
     * @throws ErrorException
     * @throws ReflectionException
     */
    public function __get(string $name): mixed
    {
        if ($this->reflection->hasProperty($name)) {
            $property = $this->reflection->getProperty($name);
            $property->setAccessible(true);

            return $property->getValue($this->targetInstance);
        }

        throw new ErrorException('ErrorException : Undefined property: ' . $name);
    }

    /**
     * @throws ErrorException
     * @throws ReflectionException
     */
    public function __set(string $name, mixed $value): void
    {
        if ($this->reflection->hasProperty($name)) {
            $property = $this->reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($this->targetInstance, $value);
        } else {
            throw new ErrorException('ErrorException : Undefined property: ' . $name);
        }
    }

    /**
     * Get reflection class
     * @return T
     */
    public function getInstance(): object
    {
        return $this->targetInstance;
    }
}
