<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\LessThanCondition;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class LessThanConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldIsLessThanValue(): void
    {
        $context   = $this->createMockContext(['field' => 5]);
        $condition = new LessThanCondition('field', 10);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsEqualToValue(): void
    {
        $context   = $this->createMockContext(['field' => 10]);
        $condition = new LessThanCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' is not less than to the expected value '10'.", $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsGreaterThanValue(): void
    {
        $context   = $this->createMockContext(['field' => 15]);
        $condition = new LessThanCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' is not less than to the expected value '10'.", $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context   = $this->createMockContext([]);
        $condition = new LessThanCondition('nonexistentField', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotComparable(): void
    {
        $context   = $this->createMockContext(['field' => 'string']);
        $condition = new LessThanCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithFloatingPointNumbers(): void
    {
        $context   = $this->createMockContext(['field' => 5.5]);
        $condition = new LessThanCondition('field', 10.1);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNull(): void
    {
        $context   = $this->createMockContext(['field' => null]);
        $condition = new LessThanCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithStringsWhenAlphabeticallyLess(): void
    {
        $context   = $this->createMockContext(['field' => 'apple']);
        $condition = new LessThanCondition('field', 'banana');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithStringsWhenAlphabeticallyGreater(): void
    {
        $context   = $this->createMockContext(['field' => 'banana']);
        $condition = new LessThanCondition('field', 'apple');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }
}
