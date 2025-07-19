<?php

declare(strict_types=1);

namespace Tests\Conditions;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\TestCase;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\CollectionCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Conditions\IfElseCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Enums\CollectionConditionType;

/**
 * @internal
 */
#[Group('Others')]
final class IfElseConditionTest extends TestCase
{
    public function testIfConditionSatisfiedExecutesThenAction(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockIfCondition = $this->createMock(ConditionInterface::class);
        $mockIfCondition->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        $mockThenAction = $this->createMock(ActionInterface::class);
        $mockThenAction->expects($this->once())
            ->method('execute')
            ->with($mockContext);

        $ifElseCondition = new IfElseCondition($mockIfCondition, $mockThenAction);
        $ifElseCondition->execute($mockContext);

        $this->assertNull($ifElseCondition->getFailureMessage());
    }

    public function testIfConditionNotSatisfiedExecutesElseAction(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockIfCondition = $this->createMock(ConditionInterface::class);
        $mockIfCondition->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);

        $mockElseAction = $this->createMock(ActionInterface::class);
        $mockElseAction->expects($this->once())
            ->method('execute')
            ->with($mockContext);

        $mockThenAction = $this->createMock(ActionInterface::class);
        $mockThenAction->expects($this->never())->method('execute');

        $ifElseCondition = new IfElseCondition($mockIfCondition, $mockThenAction, $mockElseAction);
        $ifElseCondition->execute($mockContext);

        $this->assertNull($ifElseCondition->getFailureMessage());
    }

    public function testIfConditionNotSatisfiedWithoutElseSetsFailureMessage(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockIfCondition = $this->createMock(ConditionInterface::class);
        $mockIfCondition->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);

        $mockIfCondition->expects($this->once())
            ->method('getFailureMessage')
            ->willReturn('Condition failed.');

        $mockThenAction = $this->createMock(ActionInterface::class);
        $mockThenAction->expects($this->never())->method('execute');

        $ifElseCondition = new IfElseCondition($mockIfCondition, $mockThenAction);
        $ifElseCondition->execute($mockContext);

        $this->assertSame('Condition failed.', $ifElseCondition->getFailureMessage());
    }

    public function testFactoryThrowsExceptionForMissingIfKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'if' key is missing in 'condition' node.");

        IfElseCondition::factory([
            'then' => $this->createMock(ActionInterface::class),
        ]);
    }

    public function testFactoryThrowsExceptionForInvalidIfCondition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'if' must be instance of ConditionInterface.");

        IfElseCondition::factory([
            'if'   => new stdClass(),
            'then' => $this->createMock(ActionInterface::class),
        ]);
    }

    public function testFactoryCreatesValidIfElseCondition(): void
    {
        $mockIfCondition = $this->createMock(ConditionInterface::class);
        $mockThenAction  = $this->createMock(ActionInterface::class);
        $mockElseAction  = $this->createMock(ActionInterface::class);

        $condition = IfElseCondition::factory([
            'if'   => $mockIfCondition,
            'then' => $mockThenAction,
            'else' => $mockElseAction,
        ]);

        $this->assertInstanceOf(IfElseCondition::class, $condition);
    }

    public function testComplexIfConditionWithNestedConditions(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockNestedCondition = $this->createMock(ConditionInterface::class);
        $mockNestedCondition->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        $mockIfCondition = new IfElseCondition(
            $mockNestedCondition,
            $this->createMock(ActionInterface::class),
        );

        $mockThenAction = $this->createMock(ActionInterface::class);
        $mockThenAction->expects($this->once())->method('execute')->with($mockContext);

        $ifElseCondition = new IfElseCondition($mockIfCondition, $mockThenAction);
        $ifElseCondition->execute($mockContext);

        $this->assertNull($ifElseCondition->getFailureMessage());
    }

    public function testIfConditionWithCollectionConditionAndOrLogic(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        // Mockovi za pojedinačne uslove
        $condition1 = $this->createMock(ConditionInterface::class);
        $condition1->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        $condition2 = $this->createMock(ConditionInterface::class);
        $condition2->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        $condition3 = $this->createMock(ConditionInterface::class);
        $condition3->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);

        $condition4 = $this->createMock(ConditionInterface::class);
        $condition4->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        // AND kolekcija: Svi uslovi moraju biti zadovoljeni
        $andCondition = new CollectionCondition(CollectionConditionType::AND, [$condition1, $condition2]);

        // OR kolekcija: Dovoljno je da jedan uslov bude zadovoljen
        $orCondition = new CollectionCondition(CollectionConditionType::OR, [$condition3, $condition4]);

        // Kombinacija AND i OR uslova u glavnom ifCondition
        $ifCondition = new CollectionCondition(CollectionConditionType::AND, [$andCondition, $orCondition]);

        // Mock za ThenAction
        $thenAction = $this->createMock(ActionInterface::class);
        $thenAction->expects($this->once())
            ->method('execute')
            ->with($mockContext);

        $ifElseCondition = new IfElseCondition($ifCondition, $thenAction);
        $ifElseCondition->execute($mockContext);

        // Provera
        $this->assertNull($ifElseCondition->getFailureMessage(), 'Failure message should be null because conditions are satisfied.');
    }

    public function testIfConditionWithMixedLogicAndFailureMessages(): void
    {
        $mockContext = $this->createMock(ContextInterface::class);

        // Mock za pojedinačne uslove
        $condition1 = $this->createMock(ConditionInterface::class);
        $condition1->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);
        $condition1->expects($this->once())
            ->method('getFailureMessage')
            ->willReturn('Condition 1 failed.');

        $condition2 = $this->createMock(ConditionInterface::class);
        $condition2->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        // AND kolekcija: Svi uslovi moraju biti zadovoljeni
        $condition3 = $this->createMock(ConditionInterface::class);
        $condition3->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);
        $condition3->expects($this->once())
            ->method('getFailureMessage')
            ->willReturn('Condition 3 failed.');

        $condition4 = $this->createMock(ConditionInterface::class);
        $condition4->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);

        $andCondition = new CollectionCondition(
            CollectionConditionType::AND,
            [$condition3, $condition4],
        );

        // OR kolekcija: Dovoljno je da jedan uslov bude zadovoljen
        $condition5 = $this->createMock(ConditionInterface::class);
        $condition5->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);
        $condition5->expects($this->once())
            ->method('getFailureMessage')
            ->willReturn('Condition 5 failed.');

        $condition6 = $this->createMock(ConditionInterface::class);
        $condition6->expects($this->once())
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(false);
        $condition6->expects($this->once())
            ->method('getFailureMessage')
            ->willReturn('Condition 6 failed.');

        $condition7 = $this->createMock(ConditionInterface::class);
        $condition7->expects($this->exactly(1))
            ->method('isSatisfied')
            ->with($mockContext)
            ->willReturn(true);
        $condition7->expects($this->never())
            ->method('getFailureMessage')
            ->willReturn(null);

        $orCondition = new CollectionCondition(
            CollectionConditionType::OR,
            [$condition5, $condition6],
        );

        // Glavni ifCondition sa kombinacijom AND i OR kolekcija
        $ifCondition = new CollectionCondition(
            CollectionConditionType::AND,
            [$condition1, $condition2, $andCondition, $orCondition, $condition7],
        );

        // Mock za ThenAction
        $thenAction = $this->createMock(ActionInterface::class);
        $thenAction->expects($this->never())
            ->method('execute');

        $ifElseCondition = new IfElseCondition($ifCondition, $thenAction);
        $ifElseCondition->execute($mockContext);

        // Provera poruka neuspeha
        $expectedFailureMessages = [
            'Condition 1 failed.',
            'Condition 3 failed.',
            'Condition 5 failed.',
            'Condition 6 failed.',
        ];

        $this->assertSame($expectedFailureMessages, $ifElseCondition->getFailureMessage(), 'Failure messages do not match the expected output.');
    }
}


