<?php

declare(strict_types=1);

namespace Tests\Conditions;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;
use Maniaba\RuleEngine\Conditions\NumericInRangeCondition;

/**
 * @internal
 */
#[Group('Others')]
final class NumericInRangeConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenValueInRange(): void
    {
        $context   = $this->createMockContext(['field' => 15]);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenValueBelowRange(): void
    {
        $context   = $this->createMockContext(['field' => 5]);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' must be between 10.00 and 20.00.", $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenValueAboveRange(): void
    {
        $context   = $this->createMockContext(['field' => 25]);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame("Field 'field' must be between 10.00 and 20.00.", $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context   = $this->createMockContext([]);
        $condition = new NumericInRangeCondition('nonexistentField', 10, 20);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotNumeric(): void
    {
        $context   = $this->createMockContext(['field' => 'string']);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not numeric.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenValueAtLowerBoundary(): void
    {
        $context   = $this->createMockContext(['field' => 10]);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenValueAtUpperBoundary(): void
    {
        $context   = $this->createMockContext(['field' => 20]);
        $condition = new NumericInRangeCondition('field', 10, 20);

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testFactoryThrowsExceptionForInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min and max values are required.');

        NumericInRangeCondition::factory(['contextName' => 'field']);
    }

    public function testFactoryThrowsExceptionWhenContextNameIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context name is required.');

        NumericInRangeCondition::factory(['min' => 10, 'max' => 20]);
    }
}


