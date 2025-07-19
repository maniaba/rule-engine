<?php

declare(strict_types=1);

namespace Tests\Conditions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\TestCase;
use Maniaba\RuleEngine\Context\ArrayContext;

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
}


