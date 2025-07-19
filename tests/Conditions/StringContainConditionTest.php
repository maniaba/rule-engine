<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\StringContainCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class StringContainConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenStringContainsSubstring(): void
    {
        $context = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', 'test');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringDoesNotContainSubstring(): void
    {
        $context = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', 'missing');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('String does not contain the substring "missing".', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);
        $condition = new StringContainCondition('nonexistentField', 'test');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotString(): void
    {
        $context = $this->createMockContext(['field' => 12345]);
        $condition = new StringContainCondition('field', 'test');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenStringIsEmpty(): void
    {
        $context = $this->createMockContext(['field' => '']);
        $condition = new StringContainCondition('field', 'test');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('String does not contain the substring "test".', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWhenSubstringIsEmpty(): void
    {
        $context = $this->createMockContext(['field' => 'This is a test string.']);
        $condition = new StringContainCondition('field', '');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNull(): void
    {
        $context = $this->createMockContext(['field' => null]);
        $condition = new StringContainCondition('field', 'test');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }
}
