<?php

declare(strict_types=1);

namespace Tests\Rules;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Rules\Rule;
use Tests\Support\TestCase;

/**
 * Testiranje Rule klase.
 *
 * @internal
 */
final class RuleTest extends TestCase
{
    public function testEvaluateReturnsTrueWhenConditionIsSatisfied(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $condition->method('isSatisfied')->willReturn(true);

        $rule    = new Rule($condition);
        $context = $this->createMock(ContextInterface::class);

        $this->assertTrue($rule->evaluate($context), 'Očekuje se da evaluate vrati true ako je condition zadovoljen');
    }

    public function testEvaluateReturnsFalseWhenConditionIsNotSatisfied(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $condition->method('isSatisfied')->willReturn(false);

        $rule    = new Rule($condition);
        $context = $this->createMock(ContextInterface::class);

        $this->assertFalse($rule->evaluate($context), 'Očekuje se da evaluate vrati false ako condition nije zadovoljen');
    }

    public function testExecuteRunsActionsIfConditionIsSatisfied(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $condition->method('isSatisfied')->willReturn(true);

        $action1 = $this->createMock(ActionInterface::class);
        $action1->expects($this->exactly(1))->method('execute');

        $action2 = $this->createMock(ActionInterface::class);
        $action2->expects($this->once())->method('execute');

        $elseAction = $this->createMock(ActionInterface::class);
        $elseAction->expects($this->never())->method('execute');

        $rule    = new Rule($condition, [$action1, $action2], [$elseAction]);
        $context = $this->createMock(ContextInterface::class);

        $rule->execute($context);
    }

    public function testExecuteRunsElseActionsIfConditionIsNotSatisfied(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $condition->method('isSatisfied')->willReturn(false);

        $action1 = $this->createMock(ActionInterface::class);
        $action1->expects($this->never())->method('execute');

        $action2 = $this->createMock(ActionInterface::class);
        $action2->expects($this->never())->method('execute');

        $elseAction1 = $this->createMock(ActionInterface::class);
        $elseAction1->expects($this->once())->method('execute');

        $elseAction2 = $this->createMock(ActionInterface::class);
        $elseAction2->expects($this->once())->method('execute');

        $rule    = new Rule($condition, [$action1, $action2], [$elseAction1, $elseAction2]);
        $context = $this->createMock(ContextInterface::class);

        $rule->execute($context);
    }

    public function testGetAndSetPriority(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $rule      = new Rule($condition, []);

        $this->assertSame(0, $rule->getPriority(), 'Default priority treba biti 0');

        $rule->setPriority(10);
        $this->assertSame(10, $rule->getPriority(), 'Postavljena priority vrijednost nije ispravna');
    }

    public function testGetFailureMessage(): void
    {
        $condition = $this->createMock(ConditionInterface::class);
        $condition->method('getFailureMessage')->willReturn('Nije prošao uslov');

        $rule = new Rule($condition);

        $this->assertSame('Nije prošao uslov', $rule->getFailureMessage(), 'Failure message nije ispravno vraćen');
    }
}
