<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\GreaterThanOrEqualCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class GreaterThanOrEqualConditionTest extends TestCase
{
    public function testEvaluateConditionPassesWhenValueIsGreater(): void
    {
        $contextName   = 'testField';
        $expectedValue = 10;
        $actualValue   = 15;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanOrEqualCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertTrue($result);
        $this->assertEmpty($condition->getFailureMessage());
    }

    public function testEvaluateConditionPassesWhenValueIsEqual(): void
    {
        $contextName   = 'testField';
        $expectedValue = 10;
        $actualValue   = 10;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanOrEqualCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertTrue($result);
        $this->assertEmpty($condition->getFailureMessage());
    }

    public function testEvaluateConditionFailsWhenValueIsLess(): void
    {
        $contextName   = 'testField';
        $expectedValue = 20;
        $actualValue   = 15;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanOrEqualCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertFalse($result);
        $this->assertSame("Field 'testField' is not greater than or equal to the expected value '20'.", $condition->getFailureMessage());
    }

    public function testEvaluateConditionFailsWhenFieldDoesNotExist(): void
    {
        $contextName   = 'missingField';
        $expectedValue = 10;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(false);

        $condition = new GreaterThanOrEqualCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertFalse($result);
        $this->assertSame('Field "missingField" does not exist.', $condition->getFailureMessage());
    }
}
