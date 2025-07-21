<?php

declare(strict_types=1);

namespace Tests\Factories;

use InvalidArgumentException;
use Maniaba\RuleEngine\Conditions\ArrayContainsAllCondition;
use Maniaba\RuleEngine\Conditions\ArrayContainsAnyCondition;
use Maniaba\RuleEngine\Conditions\ArrayContainsCondition;
use Maniaba\RuleEngine\Conditions\EndsWithCondition;
use Maniaba\RuleEngine\Conditions\EqualsCondition;
use Maniaba\RuleEngine\Conditions\GreaterThanCondition;
use Maniaba\RuleEngine\Conditions\GreaterThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\LessThanCondition;
use Maniaba\RuleEngine\Conditions\LessThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\NumericInRangeCondition;
use Maniaba\RuleEngine\Conditions\StartsWithCondition;
use Maniaba\RuleEngine\Conditions\StringContainCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Factories\ConditionFactory;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ConditionFactoryTest extends TestCase
{
    public function testCreateEqualsCondition(): void
    {
        $config = [
            'operator'    => 'equal',
            'contextName' => 'age',
            'value'       => 25,
        ];

        $conditionFactory = new ConditionFactory();

        $condition = $conditionFactory->create($config);

        $this->assertInstanceOf(EqualsCondition::class, $condition);

        $context = $this->createMockContext(true, 25);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 30);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateNumericInRangeCondition(): void
    {
        $config = [
            'operator'    => 'numericInRange',
            'contextName' => 'age',
            'min'         => 18,
            'max'         => 65,
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(NumericInRangeCondition::class, $condition);

        $context = $this->createMockContext(true, 25);

        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 70);

        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateArrayContainsCondition(): void
    {
        $config = [
            'operator'    => 'arrayContains',
            'contextName' => 'tags',
            'value'       => 'important',
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(ArrayContainsCondition::class, $condition);

        $context = $this->createMockContext(true, ['important', 'urgent']);

        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, ['urgent2', ['another' => 'value']]);

        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');

        // arrayContains contains another array
        $config['value'] = ['another' => 'value'];
        $condition       = (new ConditionFactory())->create($config);

        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');
    }

    public function testCreateGreaterThanCondition(): void
    {
        $config = [
            'operator'    => 'greaterThan',
            'contextName' => 'score',
            'value'       => 100,
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(GreaterThanCondition::class, $condition);

        $context = $this->createMockContext(true, 150);

        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');
    }

    public function testThrowsExceptionOnMissingOperator(): void
    {
        $config = [
            'contextName' => 'age',
            'value'       => 25,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Operator is required.');

        (new ConditionFactory())->create($config);
    }

    public function testThrowsExceptionOnMissingContextName(): void
    {
        $config = [
            'operator' => 'equal',
            'value'    => 25,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context name is required.');

        (new ConditionFactory())->create($config);
    }

    public function testThrowsExceptionOnMissingValue(): void
    {
        $config = [
            'operator'    => 'equal',
            'contextName' => 'age',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value is required.');

        (new ConditionFactory())->create($config);
    }

    public function testThrowsExceptionOnUnsupportedOperator(): void
    {
        $config = [
            'operator'    => 'unsupportedOperator',
            'contextName' => 'age',
            'value'       => 25,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported operator: unsupportedOperator');

        (new ConditionFactory())->create($config);
    }

    public function testCreateGreaterThanOrEqualCondition(): void
    {
        $config = [
            'operator'    => 'greaterThanOrEqual',
            'contextName' => 'age',
            'value'       => 18,
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(GreaterThanOrEqualCondition::class, $condition);

        $context = $this->createMockContext(true, 20);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 17);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateLessThanOrEqualCondition(): void
    {
        $config = [
            'operator'    => 'lessThanOrEqual',
            'contextName' => 'age',
            'value'       => 18,
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(LessThanOrEqualCondition::class, $condition);

        $context = $this->createMockContext(true, 18);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 19);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateStringContainCondition(): void
    {
        $config = [
            'operator'    => 'contains',
            'contextName' => 'description',
            'value'       => 'important',
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(StringContainCondition::class, $condition);

        $context = $this->createMockContext(true, 'This is an important message.');
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 'This is a regular message.');
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateArrayContainsAllCondition(): void
    {
        $config = [
            'operator'    => 'arrayContainsAll',
            'contextName' => 'tags',
            'value'       => ['urgent', 'important'],
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(ArrayContainsAllCondition::class, $condition);

        $context = $this->createMockContext(true, ['urgent', 'important', 'todo']);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, ['urgent']);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateArrayContainsAnyCondition(): void
    {
        $config = [
            'operator'    => 'arrayContainsAny',
            'contextName' => 'tags',
            'value'       => ['urgent', 'important'],
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(ArrayContainsAnyCondition::class, $condition);

        $context = $this->createMockContext(true, ['urgent', 'todo']);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, ['todo']);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateEndsWithCondition(): void
    {
        $config = [
            'operator'    => 'endsWith',
            'contextName' => 'fileName',
            'value'       => '.pdf',
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(EndsWithCondition::class, $condition);

        $context = $this->createMockContext(true, 'document.pdf');
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 'document.docx');
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateStartsWithCondition(): void
    {
        $config = [
            'operator'    => 'startsWith',
            'contextName' => 'userName',
            'value'       => 'admin',
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(StartsWithCondition::class, $condition);

        $context = $this->createMockContext(true, 'adminUser');
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 'userAdmin');
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    public function testCreateLessThanCondition(): void
    {
        $config = [
            'operator'    => 'lessThan',
            'contextName' => 'score',
            'value'       => 50,
        ];

        $condition = (new ConditionFactory())->create($config);

        $this->assertInstanceOf(LessThanCondition::class, $condition);

        $context = $this->createMockContext(true, 40);
        $this->assertTrue($condition->isSatisfied($context), 'Condition should be satisfied');

        $context = $this->createMockContext(true, 60);
        $this->assertFalse($condition->isSatisfied($context), 'Condition should not be satisfied');
    }

    private function createMockContext(bool $hasField, mixed $field): ContextInterface
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('hasField')->willReturn($hasField);
        $context->method('getField')->willReturn($field);

        return $context;
    }
}
