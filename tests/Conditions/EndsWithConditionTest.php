<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\EndsWithCondition;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class EndsWithConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWhenFieldEndsWithSuffix(): void
    {
        $context = $this->createMockContext(['field' => 'example.com']);

        $condition = EndsWithCondition::factory([
            'contextName' => 'field',
            'value' => '.com',
        ]);

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotEndWithSuffix(): void
    {
        $context = $this->createMockContext(['field' => 'example.org']);
        $condition = new EndsWithCondition('field', '.com');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not end with the suffix ".com".', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNotString(): void
    {
        $context = $this->createMockContext(['field' => 12345]);
        $condition = new EndsWithCondition('field', '45');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldDoesNotExist(): void
    {
        $context = $this->createMockContext([]);
        $condition = new EndsWithCondition('field', '.com');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not exist.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithEmptySuffix(): void
    {
        $context = $this->createMockContext(['field' => 'example']);
        $condition = new EndsWithCondition('field', '');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWhenFieldIsNull(): void
    {
        $context = $this->createMockContext(['field' => null]);
        $condition = new EndsWithCondition('field', '.com');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" is not a valid string.', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithWhitespaceSuffix(): void
    {
        $context = $this->createMockContext(['field' => 'example ']);
        $condition = new EndsWithCondition('field', ' ');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithWhitespaceSuffix(): void
    {
        $context = $this->createMockContext(['field' => 'example']);
        $condition = new EndsWithCondition('field', ' ');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not end with the suffix " ".', $condition->getFailureMessage());
    }

    public function testConditionSatisfiedWithCaseSensitiveMatch(): void
    {
        $context = $this->createMockContext(['field' => 'Example.COM']);
        $condition = new EndsWithCondition('field', '.COM');

        self::assertTrue($condition->isSatisfied($context));
        self::assertNull($condition->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithCaseSensitiveMismatch(): void
    {
        $context = $this->createMockContext(['field' => 'example.com']);
        $condition = new EndsWithCondition('field', '.COM');

        self::assertFalse($condition->isSatisfied($context));
        self::assertSame('Field "field" does not end with the suffix ".COM".', $condition->getFailureMessage());
    }
}
