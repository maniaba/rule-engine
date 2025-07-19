<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\EqualsCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class EqualsConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldEqualsValue(): void
    {
        $context = $this->createMockContext(['field' => 'value']);
        $condition = new EqualsCondition('field', 'value');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotEqualValue(): void
    {
        $context = $this->createMockContext(['field' => 'differentValue']);
        $condition = new EqualsCondition('field', 'value');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame(
            "Field 'field' does not equal the expected value 'value'.",
            $condition->getFailureMessage(),
        );
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);
        $condition = new EqualsCondition('nonexistentField', 'value');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "nonexistentField" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithStrictTypeMatch(): void
    {
        $context = $this->createMockContext(['field' => 123]);
        $condition = new EqualsCondition('field', 123);

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithStrictTypeMismatch(): void
    {
        $context = $this->createMockContext(['field' => '123']);
        $condition = new EqualsCondition('field', 123);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame(
            "Field 'field' does not equal the expected value '123'.",
            $condition->getFailureMessage(),
        );
    }

    public function testConditionSatisfiedWithNullValue(): void
    {
        $context = $this->createMockContext(['field' => null]);
        $condition = new EqualsCondition('field', null);

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithNullValueMismatch(): void
    {
        $context = $this->createMockContext(['field' => 'value']);
        $condition = new EqualsCondition('field', null);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame(
            "Field 'field' does not equal the expected value ''.",
            $condition->getFailureMessage(),
        );
    }

    public function testConditionSatisfiedWithArrayMatch(): void
    {
        $context = $this->createMockContext(['field' => ['key' => 'value']]);
        $condition = new EqualsCondition('field', ['key' => 'value']);

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithArrayMismatch(): void
    {
        $context = $this->createMockContext(['field' => ['key' => 'value']]);
        $condition = new EqualsCondition('field', ['key' => 'differentValue']);

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame(
            "Field 'field' does not equal the expected value 'Array'.",
            $condition->getFailureMessage(),
        );
    }
}
