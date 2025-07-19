<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Context\ArrayContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[CoversClass(ArrayContext::class)]
#[Group('Others')]
final class ArrayContextTest extends TestCase
{
    #[DataProvider('provideGetField')]
    public function testGetField(string $field, mixed $value, mixed $expected): void
    {
        $context = new ArrayContext([$field => $value]);

        self::assertSame($expected, $context->getField($field), 'Field value mismatch.');

        self::assertTrue($context->hasField($field), 'Field not found.');
    }

    public static function provideGetField(): iterable
    {
        $std = new \stdClass();
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

        self::assertTrue($context->hasField('foo'));
        self::assertFalse($context->hasField('baz'));
    }
}
