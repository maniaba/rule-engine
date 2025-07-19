<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\StartsWithCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class StartsWithConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenStringStartsWithPrefix(): void
    {
        $context   = $this->createMockContext(['field' => 'prefix-value']);
        $condition = new StartsWithCondition('field', 'prefix');

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringDoesNotStartWithPrefix(): void
    {
        $context   = $this->createMockContext(['field' => 'value']);
        $condition = new StartsWithCondition('field', 'prefix');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('String does not start with the prefix "prefix".', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context   = $this->createMockContext([]);
        $condition = new StartsWithCondition('nonexistentField', 'prefix');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotString(): void
    {
        $context   = $this->createMockContext(['field' => 12345]);
        $condition = new StartsWithCondition('field', 'prefix');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenStringEqualsPrefix(): void
    {
        $context   = $this->createMockContext(['field' => 'prefix']);
        $condition = new StartsWithCondition('field', 'prefix');

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringIsEmpty(): void
    {
        $context   = $this->createMockContext(['field' => '']);
        $condition = new StartsWithCondition('field', 'prefix');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('String does not start with the prefix "prefix".', $condition->getFailureMessage());
    }
}
