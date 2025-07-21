<?php

declare(strict_types=1);

namespace Tests\Context;

use JsonException;
use Maniaba\RuleEngine\Context\ObjectContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ObjectContextTest extends TestCase
{
    public function testGetFieldReturnsValueIfExists(): void
    {
        $obj     = (object) ['foo' => 'bar'];
        $context = new ObjectContext($obj);
        $this->assertSame('bar', $context->getField('foo'));
    }

    public function testGetFieldReturnsNullIfNotExists(): void
    {
        $obj     = (object) [];
        $context = new ObjectContext($obj);
        $this->assertNull($context->getField('missing'));
    }

    public function testHasFieldReturnsTrueIfExists(): void
    {
        $obj     = (object) ['foo' => 123];
        $context = new ObjectContext($obj);
        $this->assertTrue($context->hasField('foo'));
    }

    public function testHasFieldReturnsFalseIfNotExists(): void
    {
        $obj     = (object) [];
        $context = new ObjectContext($obj);
        $this->assertFalse($context->hasField('foo'));
    }

    public function testGetObjectReturnsOriginalObject(): void
    {
        $obj     = (object) ['a' => 1];
        $context = new ObjectContext($obj);
        $this->assertSame($obj, $context->getObject());
    }

    public function testToStringReturnsJson(): void
    {
        $obj     = (object) ['x' => 1, 'y' => 2];
        $context = new ObjectContext($obj);
        $json    = (string) $context;
        $this->assertJson($json);
        $this->assertSame(json_encode($obj, JSON_THROW_ON_ERROR), $json);
    }

    public function testToStringThrowsOnJsonError(): void
    {
        $obj = (object) ['invalid' => INF];
        $context = new ObjectContext($obj);
        $this->expectException(JsonException::class);
        $cast = (string) $context;

        $this->assertNotFalse($cast, 'Expected to throw JsonException on invalid JSON conversion');
    }
}
