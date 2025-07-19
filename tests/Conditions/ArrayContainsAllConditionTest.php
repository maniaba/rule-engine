<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\ArrayContainsAllCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayContainsAllConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenAllValuesArePresent(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value2', 'value3']]);

        $condition = new ArrayContainsAllCondition('field', ['value1', 'value2']);

        self::assertTrue($condition->isSatisfied($context));
    }

    public function testConditionNotSatisfiedWhenSomeValuesAreMissing(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value3']]);

        $condition = new ArrayContainsAllCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
    }

    public function testConditionNotSatisfiedWhenFieldIsNotArray(): void
    {
        $context = $this->createMockContext(['field' => 'not_an_array']);

        $condition = new ArrayContainsAllCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);

        $condition = new ArrayContainsAllCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
    }

    public function testFailureMessage(): void
    {
        $context = $this->createMockContext(['field' => ['value1']]);

        $condition = new ArrayContainsAllCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not contain all values.', $condition->getFailureMessage());
    }

    public function testFieldContainsAllValues(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('hasField')->with('testField')->willReturn(true);
        $context->method('getField')->with('testField')->willReturn(['value1', 'value2', 'value3']);

        $condition = new ArrayContainsAllCondition('testField', ['value1', 'value2']);
        self::assertTrue($condition->isSatisfied($context));
    }

    public function testFieldDoesNotContainAllValues(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('hasField')->with('testField')->willReturn(true);
        $context->method('getField')->with('testField')->willReturn(['value1', 'value3']);

        $condition = new ArrayContainsAllCondition('testField', ['value1', 'value2']);
        self::assertFalse($condition->isSatisfied($context));
    }

    public function testFieldDoesNotExist(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('hasField')->with('testField')->willReturn(false);

        $condition = new ArrayContainsAllCondition('testField', ['value1', 'value2']);
        self::assertFalse($condition->isSatisfied($context));
    }

    public function testFieldIsNotArray(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('hasField')->with('testField')->willReturn(true);
        $context->method('getField')->with('testField')->willReturn('not an array');

        $condition = new ArrayContainsAllCondition('testField', ['value1', 'value2']);
        self::assertFalse($condition->isSatisfied($context));
    }
}
