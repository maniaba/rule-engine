<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Conditions\CollectionCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Conditions\EqualsCondition;
use Maniaba\RuleEngine\Conditions\NotCondition;
use Maniaba\RuleEngine\Context\ArrayContext;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Enums\CollectionConditionType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class NotConditionTest extends TestCase
{
    use MockContextTrait;

    public function testNegation(): void
    {
        $context = new ArrayContext(['depositCount' => 3]);

        $equalsCondition = new EqualsCondition('depositCount', 5);
        $notCondition = new NotCondition($equalsCondition);

        self::assertTrue($notCondition->isSatisfied($context), 'Negation failed');
        self::assertFalse($equalsCondition->isSatisfied($context), 'EqualsCondition failed');
    }

    public function testConditionWithStringFailureMessage(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockCondition = $this->createMock(ConditionInterface::class);
        $mockCondition->expects(self::once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true)
        ;
        $mockCondition->expects(self::once())
            ->method('getFailureMessage')
            ->willReturn('Original condition failed.')
        ;

        $notCondition = new NotCondition($mockCondition);

        self::assertFalse($notCondition->isSatisfied($mockContext));
        self::assertSame('Negation of: Original condition failed.', $notCondition->getFailureMessage());
    }

    public function testConditionWithArrayFailureMessage(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockCondition = $this->createMock(ConditionInterface::class);
        $mockCondition->expects(self::once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true)
        ;
        $mockCondition->expects(self::once())
            ->method('getFailureMessage')
            ->willReturn([
                'Condition 1 failed.',
                'Condition 2 failed.',
            ])
        ;

        $notCondition = new NotCondition($mockCondition);

        self::assertFalse($notCondition->isSatisfied($mockContext));
        self::assertSame(
            [
                'Negation of: Condition 1 failed.',
                'Negation of: Condition 2 failed.',
            ],
            $notCondition->getFailureMessage(),
        );
    }

    public function testConditionWithNullFailureMessage(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockCondition = $this->createMock(ConditionInterface::class);
        $mockCondition->expects(self::once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true)
        ;
        $mockCondition->expects(self::once())
            ->method('getFailureMessage')
            ->willReturn(null)
        ;

        $notCondition = new NotCondition($mockCondition);

        self::assertFalse($notCondition->isSatisfied($mockContext));
        self::assertNull($notCondition->getFailureMessage());
    }

    public function testComplexConditionWithNegationAndCollectionConditions(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        // Uslov 1 (prolazi)
        $condition1 = $this->createMock(ConditionInterface::class);
        $condition1->expects(self::exactly(2))
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false)
        ;
        $condition1->expects(self::exactly(2))
            ->method('getFailureMessage')
            ->willReturn('Condition 1 failed.')
        ;

        // Uslov 2 (ne prolazi)
        $condition2 = $this->createMock(ConditionInterface::class);
        $condition2->expects(self::exactly(2))
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true)
        ;

        // AND Kolekcija
        $andCollection = new CollectionCondition(
            CollectionConditionType::AND,
            [$condition1, $condition2], // Jedan prolazi, drugi ne prolazi
        );

        $andCollection->isSatisfied($mockContext);

        self::assertSame([
            'Condition 1 failed.',
        ], $andCollection->getFailureMessage());

        // Negacija AND kolekcije
        $negatedAndCollection = new NotCondition($andCollection);
        $negatedAndCollection->isSatisfied($mockContext);

        self::assertNull($negatedAndCollection->getFailureMessage());
    }
}
