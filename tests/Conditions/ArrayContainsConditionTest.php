<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\ArrayContainsCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayContainsConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldContainsValue(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value2', 'value3']]);
        $condition = new ArrayContainsCondition('field', 'value2');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotContainValue(): void
    {
        $context = $this->createMockContext(['field' => ['value1', 'value3']]);
        $condition = new ArrayContainsCondition('field', 'value2');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not contain value.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotArray(): void
    {
        $context = $this->createMockContext(['field' => 'not_an_array']);
        $condition = new ArrayContainsCondition('field', 'value1');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not an array.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);
        $condition = new ArrayContainsCondition('field', 'value1');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not exist.', $condition->getFailureMessage());
    }
}
