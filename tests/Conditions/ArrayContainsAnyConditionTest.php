<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\ArrayContainsAnyCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayContainsAnyConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldContainsAnyValue(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value2', 'value3']]);
        $condition = new ArrayContainsAnyCondition('field', ['value2', 'value4']);

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotContainAnyValue(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value3']]);
        $condition = new ArrayContainsAnyCondition('field', ['value4', 'value5']);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not contain any of the values.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotArray(): void
    {
        $context = $this->createMockContext(['field' => 'not_an_array']);
        $condition = new ArrayContainsAnyCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not an array.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);
        $condition = new ArrayContainsAnyCondition('field', ['value1', 'value2']);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenValuesAreEmpty(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value2']]);
        $condition = new ArrayContainsAnyCondition('field', []);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not contain any of the values.', $condition->getFailureMessage());
    }
}
