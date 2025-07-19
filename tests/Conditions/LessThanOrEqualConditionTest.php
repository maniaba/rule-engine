<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\LessThanOrEqualCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class LessThanOrEqualConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldIsLessThanValue(): void
    {
        $context   = $this->createMockContext(['field' => 5]);
        $condition = new LessThanOrEqualCondition('field', 10);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenFieldIsEqualToValue(): void
    {
        $context   = $this->createMockContext(['field' => 10]);
        $condition = new LessThanOrEqualCondition('field', 10);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsGreaterThanValue(): void
    {
        $context   = $this->createMockContext(['field' => 15]);
        $condition = new LessThanOrEqualCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' is not less than or equal to the expected value '10'.", $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context   = $this->createMockContext([]);
        $condition = new LessThanOrEqualCondition('nonexistentField', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotComparable(): void
    {
        $context   = $this->createMockContext(['field' => 'string']);
        $condition = new LessThanOrEqualCondition('field', 10);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithFloatingPointNumbers(): void
    {
        $context   = $this->createMockContext(['field' => 5.5]);
        $condition = new LessThanOrEqualCondition('field', 10.1);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithFloatingPointNumbers(): void
    {
        $context   = $this->createMockContext(['field' => 10.2]);
        $condition = new LessThanOrEqualCondition('field', 10.1);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' is not less than or equal to the expected value '10.1'.", $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenFieldIsNullAndLessThan(): void
    {
        $context   = $this->createMockContext(['field' => null]);
        $condition = new LessThanOrEqualCondition('field', 0);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not comparable.', $condition->getFailureMessage());
    }
}
