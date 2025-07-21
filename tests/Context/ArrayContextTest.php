<?php

declare(strict_types=1);

namespace Context;

use Maniaba\RuleEngine\Context\ArrayContext;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ArrayContextTest extends TestCase
{
    #[DataProvider('provideGetField')]
    public function testGetField(string $field, mixed $value, mixed $expected): void
    {
        $context = new ArrayContext([$field => $value]);

        $this->assertSame($expected, $context->getField($field), 'Field value mismatch.');

        $this->assertTrue($context->hasField($field), 'Field not found.');
    }

    public static function provideGetField(): iterable
    {
        $std      = new stdClass();
        $std->foo = 'bar';

        yield 'foo -> bar' => ['foo', 'bar', 'bar'];

        yield 'null value' => ['baz', null, null];

        yield 'null value 2' => ['foo', null, null];

        yield 'bool value' => ['foo', false, false];

        yield 'int value' => ['foo', 0, 0];

        yield 'empty string value' => ['foo', '', ''];

        yield 'empty array value' => ['foo', [], []];

        yield 'stdClass value' => ['foo', $std, $std];

        yield 'array value' => ['foo', ['bar'], ['bar']];

        yield 'array multidimensional value' => ['foo', ['bar' => 'baz'], ['bar' => 'baz']];
    }

    public function testHasField(): void
    {
        $context = new ArrayContext(['foo' => 'bar']);

        $this->assertTrue($context->hasField('foo'));
        $this->assertFalse($context->hasField('baz'));
    }

    public function testToString(): void
    {
        $data    = ['foo' => 'bar', 'baz' => 1];
        $context = new ArrayContext($data);
        $this->assertJsonStringEqualsJsonString(json_encode($data, JSON_THROW_ON_ERROR), (string) $context);
    }

    public function testGetDataAndToArray(): void
    {
        $data    = ['a' => 1, 'b' => 2];
        $context = new ArrayContext($data);
        $this->assertSame($data, $context->getData());
        $this->assertSame($data, $context->toArray());
    }

    public function testSetField(): void
    {
        $context = new ArrayContext([]);
        $context->setField('foo', 'bar');
        $this->assertSame('bar', $context->getField('foo'));
    }

    public function testRemoveField(): void
    {
        $context = new ArrayContext(['foo' => 'bar', 'baz' => 1]);
        $context->removeField('foo');
        $this->assertFalse($context->hasField('foo'));
        $this->assertTrue($context->hasField('baz'));
    }

    public function testClear(): void
    {
        $context = new ArrayContext(['foo' => 'bar']);
        $context->clear();
        $this->assertTrue($context->isEmpty());
        $this->assertSame([], $context->getData());
    }

    public function testMerge(): void
    {
        $context = new ArrayContext(['a' => 1]);
        $context->merge(['b' => 2, 'a' => 3]);
        $this->assertSame(['a' => 3, 'b' => 2], $context->getData());
    }

    public function testGetKeysAndValues(): void
    {
        $context = new ArrayContext(['x' => 10, 'y' => 20]);
        $this->assertSame(['x', 'y'], $context->getKeys());
        $this->assertSame([10, 20], $context->getValues());
    }

    public function testCountAndIsEmpty(): void
    {
        $context = new ArrayContext([]);
        $this->assertSame(0, $context->count());
        $this->assertTrue($context->isEmpty());
        $context->setField('foo', 'bar');
        $this->assertSame(1, $context->count());
        $this->assertFalse($context->isEmpty());
    }

    public function testFilter(): void
    {
        $context  = new ArrayContext(['a' => 1, 'b' => 2, 'c' => 3]);
        $filtered = $context->filter(static fn ($v) => $v % 2 === 1);
        $this->assertSame(['a' => 1, 'c' => 3], $filtered);
    }
}
