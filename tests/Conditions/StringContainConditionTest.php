<?php

declare(strict_types=1);

namespace Tests\Conditions;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;
use Maniaba\RuleEngine\Conditions\StringContainCondition;

/**
 * @internal
 */
#[Group('Others')]
final class StringContainConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenStringContainsSubstring(): void
    {
        $context   = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', 'test');

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringDoesNotContainSubstring(): void
    {
        $context   = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', 'missing');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('String does not contain the substring "missing".', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context   = $this->createMockContext([]);
        $condition = new StringContainCondition('nonexistentField', 'test');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotString(): void
    {
        $context   = $this->createMockContext(['field' => 12345]);
        $condition = new StringContainCondition('field', 'test');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringIsEmpty(): void
    {
        $context   = $this->createMockContext(['field' => '']);
        $condition = new StringContainCondition('field', 'test');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('String does not contain the substring "test".', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenSubstringIsEmpty(): void
    {
        $context   = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', '');

        $this->assertTrue($condition->isSatisfied($context));
        $this->assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNull(): void
    {
        $context   = $this->createMockContext(['field' => null]);
        $condition = new StringContainCondition('field', 'test');

        $this->assertFalse($condition->isSatisfied($context));
        $this->assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }
}


