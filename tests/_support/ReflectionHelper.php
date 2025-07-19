<?php

declare(strict_types=1);

namespace Tests\Support;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

trait ReflectionHelper
{
    /**
     * Find a private method invoker.
     *
     * @param object|string $obj    object or class name
     * @param string        $method method name
     *
     * @return Closure(mixed ...$args):mixed
     *
     * @throws ReflectionException
     */
    public static function getPrivateMethodInvoker(object|string $obj, string $method): Closure
    {
        $refMethod = new ReflectionMethod($obj, $method);
        $obj       = (\gettype($obj) === 'object') ? $obj : null;

        return static fn (...$args): mixed => $refMethod->invokeArgs($obj, $args);
    }

    /**
     * Set a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     * @param mixed         $value    value
     *
     * @throws ReflectionException
     */
    public static function setPrivateProperty(object|string $obj, string $property, mixed $value): void
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        if (\is_object($obj)) {
            $refProperty->setValue($obj, $value);
        } else {
            $refProperty->setValue(null, $value);
        }
    }

    /**
     * Retrieve a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     *
     * @throws ReflectionException
     */
    public static function getPrivateProperty(object|string $obj, string $property): mixed
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        return \is_string($obj) ? $refProperty->getValue() : $refProperty->getValue($obj);
    }

    /**
     * Find an accessible property.
     *
     * @throws ReflectionException
     */
    private static function getAccessibleRefProperty(object|string $obj, string $property): ReflectionProperty
    {
        $refClass = \is_object($obj) ? new ReflectionObject($obj) : new ReflectionClass($obj);

        return $refClass->getProperty($property);
    }
}
