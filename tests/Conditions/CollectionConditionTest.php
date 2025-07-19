<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\CollectionCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Enums\CollectionConditionType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class CollectionConditionTest extends TestCase
{
    use MockContextTrait;

    public function testConditionSatisfiedWithAndAllConditionsPass(): void
    {
        $context = $this->createMockContext([]);
        $condition1 = $this->createMockCondition(true);
        $condition2 = $this->createMockCondition(true);

        $collection = new CollectionCondition(CollectionConditionType::AND, [$condition1, $condition2]);

        self::assertTrue($collection->isSatisfied($context));
        self::assertNull($collection->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithAndOneConditionFails(): void
    {
        $context = $this->createMockContext([]);
        $condition1 = $this->createMockCondition(true);
        $condition2 = $this->createMockCondition(false, 'Condition 2 failed');

        $collection = new CollectionCondition(CollectionConditionType::AND, [$condition1, $condition2]);

        self::assertFalse($collection->isSatisfied($context));
        self::assertSame(['Condition 2 failed'], $collection->getFailureMessage());
    }

    public function testConditionSatisfiedWithOrOneConditionPasses(): void
    {
        $context = $this->createMockContext([]);
        $condition1 = $this->createMockCondition(false, 'Condition 1 failed');
        $condition2 = $this->createMockCondition(true);

        $collection = new CollectionCondition(CollectionConditionType::OR, [$condition1, $condition2]);

        self::assertTrue($collection->isSatisfied($context));
        self::assertNull($collection->getFailureMessage());
    }

    public function testConditionNotSatisfiedWithOrAllConditionsFail(): void
    {
        $context = $this->createMockContext([]);
        $condition1 = $this->createMockCondition(false, 'Condition 1 failed');
        $condition2 = $this->createMockCondition(false, 'Condition 2 failed');

        $collection = new CollectionCondition(CollectionConditionType::OR, [$condition1, $condition2]);

        self::assertFalse($collection->isSatisfied($context));
        self::assertSame(['Condition 1 failed', 'Condition 2 failed'], $collection->getFailureMessage());
    }

    public function testEmptyConditionsAlwaysPassForAnd(): void
    {
        $context = $this->createMockContext([]);
        $collection = new CollectionCondition(CollectionConditionType::AND, []);

        self::assertTrue($collection->isSatisfied($context));
        self::assertNull($collection->getFailureMessage());
    }

    public function testEmptyConditionsAlwaysFailForOr(): void
    {
        $context = $this->createMockContext([]);
        $collection = new CollectionCondition(CollectionConditionType::OR, []);

        self::assertFalse($collection->isSatisfied($context));
        self::assertNull($collection->getFailureMessage());
    }

    public function testInvalidConditionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition must implement ConditionInterface.');

        $invalidCondition = new \stdClass();
        $collection = new CollectionCondition(CollectionConditionType::AND, [$invalidCondition]);

        $context = $this->createMockContext([]);
        $collection->isSatisfied($context);
    }

    public function testComplexConditionWithNestedCollections(): void
    {
        $context = $this->createMockContext([
            'field1' => ['value1', 'value3'],
            'field2' => ['value4'],
            'field3' => ['value6'],
        ]);

        // Glavna kolekcija sa AND logikom
        $nestedCondition1 = $this->createMockCondition(false, 'Nested Condition 1 failed');
        $nestedCondition2 = $this->createMockCondition(true);

        $nestedCollection = new CollectionCondition(
            CollectionConditionType::AND,
            [$nestedCondition1, $nestedCondition2], // OR logika, prolazi jer je drugi uslov zadovoljen
        );

        $mainCondition1 = $this->createMockCondition(true);
        $mainCondition2 = $this->createMockCondition(false, 'Main Condition 2 failed');

        // Glavna kolekcija
        $mainCollection = new CollectionCondition(
            CollectionConditionType::AND,
            [$mainCondition1, $mainCondition2, $nestedCollection],
        );

        self::assertFalse($mainCollection->isSatisfied($context));

        // Glavna kolekcija treba da vrati poruke neuspeha
        $expectedMessages = [
            'Main Condition 2 failed',
            'Nested Condition 1 failed', // Poruka iz podkolekcije
        ];

        self::assertSame($expectedMessages, $mainCollection->getFailureMessage());
    }

    public function testComplexConditionWithMultipleNestedCollections(): void
    {
        $context = $this->createMockContext([
            'field1' => ['value1', 'value2'],
            'field2' => ['value3'],
            'field3' => ['value4'],
        ]);

        // Kreiranje ugnježdene kolekcije sa AND logikom
        $nestedCondition1 = $this->createMockCondition(false, 'Nested Condition 1 failed');
        $nestedCondition2 = $this->createMockCondition(true);
        $nestedCondition3 = $this->createMockCondition(false, 'Nested Condition 3 failed');

        $nestedCollection = new CollectionCondition(
            CollectionConditionType::AND,
            [$nestedCondition1, $nestedCondition2, $nestedCondition3],
        );

        // Kreiranje druge kolekcije sa OR logikom
        $orCondition1 = $this->createMockCondition(false, 'OR Condition 1 failed');
        $orCondition2 = $this->createMockCondition(true);

        $orCollection = new CollectionCondition(
            CollectionConditionType::OR,
            [$orCondition1, $orCondition2],
        );

        // Glavna kolekcija sa AND logikom
        $mainCondition1 = $this->createMockCondition(true);
        $mainCondition2 = $this->createMockCondition(false, 'Main Condition 2 failed');

        $mainCollection = new CollectionCondition(
            CollectionConditionType::AND,
            [$mainCondition1, $mainCondition2, $nestedCollection, $orCollection],
        );

        // Evaluacija glavne kolekcije
        self::assertFalse($mainCollection->isSatisfied($context));

        // Očekivane poruke neuspeha
        $expectedMessages = [
            'Main Condition 2 failed',
            'Nested Condition 1 failed',
            'Nested Condition 3 failed',
        ];

        self::assertSame($expectedMessages, $mainCollection->getFailureMessage());
    }

    private function createMockCondition(bool $isSatisfied, ?string $failureMessage = null): ConditionInterface
    {
        $mock = $this->createMock(ConditionInterface::class);
        $mock->method('isSatisfied')->willReturn($isSatisfied);
        $mock->method('getFailureMessage')->willReturn($failureMessage);

        return $mock;
    }
}
