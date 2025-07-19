<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\GreaterThanCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class GreaterThanConditionTest extends TestCase
{
    public function testEvaluateConditionPassesWhenValueIsGreater(): void
    {
        $contextName   = 'testField';
        $expectedValue = 10;
        $actualValue   = 15;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertTrue($result);
        $this->assertEmpty($condition->getFailureMessage());
    }

    public function testEvaluateConditionFailsWhenValueIsNotGreater(): void
    {
        $contextName   = 'testField';
        $expectedValue = 20;
        $actualValue   = 15;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertFalse($result);
        $this->assertSame("Field 'testField' is not greater than to the expected value '20'.", $condition->getFailureMessage());
    }

    public function testEvaluateConditionFailsWhenFieldDoesNotExist(): void
    {
        $contextName   = 'missingField';
        $expectedValue = 10;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(false);

        $condition = new GreaterThanCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertFalse($result);
        $this->assertSame('Field "missingField" does not exist.', $condition->getFailureMessage());
    }

    public function testEvaluateConditionFailsWhenValueIsNull(): void
    {
        $contextName   = 'testField';
        $expectedValue = 15;
        $actualValue   = null;

        $mockContext = $this->createMock(ContextInterface::class);
        $mockContext->method('hasField')->with($contextName)->willReturn(true);
        $mockContext->method('getField')->with($contextName)->willReturn($actualValue);

        $condition = new GreaterThanCondition($contextName, $expectedValue);
        $result    = $condition->isSatisfied($mockContext);

        $this->assertFalse($result);
        $this->assertSame('Field "testField" is not comparable.', $condition->getFailureMessage());
    }
}
