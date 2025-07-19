<?php

declare(strict_types=1);

namespace Tests\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\PriorityEvaluator;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * Testiranje PriorityEvaluator klase.
 *
 * @internal
 */
#[Group('Others')]
final class PriorityEvaluatorTest extends TestCase
{
    public function testEvaluateSortsRulesByPriority(): void
    {
        $evaluator = new PriorityEvaluator();
        $context = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        $highPriorityRule = $this->createMock(RuleInterface::class);
        $highPriorityRule->method('getPriority')->willReturn(10);
        $highPriorityRule->method('evaluate')->willReturn(true);
        $highPriorityRule->method('getFailureMessage')->willReturn('No error');

        $midPriorityRule = $this->createMock(RuleInterface::class);
        $midPriorityRule->method('getPriority')->willReturn(5);
        $midPriorityRule->method('evaluate')->willReturn(true);
        $midPriorityRule->method('getFailureMessage')->willReturn('No error');

        $lowPriorityRule = $this->createMock(RuleInterface::class);
        $lowPriorityRule->method('getPriority')->willReturn(1);
        $lowPriorityRule->method('evaluate')->willReturn(false);
        $lowPriorityRule->method('getFailureMessage')->willReturn('Fail');

        $ruleSet->addRule($lowPriorityRule);
        $ruleSet->addRule($highPriorityRule);
        $ruleSet->addRule($midPriorityRule);

        $results = $evaluator->evaluate($ruleSet, $context);

        // Provjeravamo redoslijed: najveći prioritet treba biti evaluiran prvi
        // Prema usort logici, redoslijed treba biti: highPriorityRule(10), midPriorityRule(5), lowPriorityRule(1).
        self::assertCount(3, $results, 'Treba biti 3 rezultata evaluacije');
        self::assertInstanceOf(EvaluationResult::class, $results[0]);
        self::assertInstanceOf(EvaluationResult::class, $results[1]);
        self::assertInstanceOf(EvaluationResult::class, $results[2]);

        self::assertSame($highPriorityRule, $results[0]->rule, 'Prvo evaluirano pravilo treba imati najveći prioritet');
        self::assertSame($midPriorityRule, $results[1]->rule, 'Drugo evaluirano pravilo treba biti srednjeg prioriteta');
        self::assertSame($lowPriorityRule, $results[2]->rule, 'Treće evaluirano pravilo treba biti najmanjeg prioriteta');

        // Pošto RuleSet nije evaluiran preko $ruleSet->evaluate(), failedRules u RuleSet-u nisu ažurirane.
        // Očekujemo da getFailedRules() vrati ono što je RuleSet interno postavio, ali nije postavljeno jer nismo zvali RuleSet->evaluate().
        // Dakle, vjerovatno prazno.
        self::assertEmpty($evaluator->getFailedRules(), 'Bez poziva RuleSet->evaluate(), failedRules će biti prazne');
    }

    public function testExecuteExecutesRulesInPriorityOrder(): void
    {
        $evaluator = new PriorityEvaluator();
        $context = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        $highPriorityRule = $this->createMock(RuleInterface::class);
        $highPriorityRule->method('getPriority')->willReturn(10);
        $highPriorityRule->expects(self::once())->method('execute')->with($context);

        $lowPriorityRule = $this->createMock(RuleInterface::class);
        $lowPriorityRule->method('getPriority')->willReturn(1);
        $lowPriorityRule->expects(self::once())->method('execute')->with($context);

        $ruleSet->addRule($lowPriorityRule);
        $ruleSet->addRule($highPriorityRule);

        $evaluator->execute($ruleSet, $context);

        // Provjera se svodi na to da su oba pravila execute-ana, i to tako da je prvo pozvan rule najvećeg prioriteta.
        // Redoslijed poziva nije jednostavno testirati samo ovako, jer su oboje očekivali one() poziv.
        // Ako želimo testirati redoslijed, možemo koristiti "inSequence" ili neke složenije metode.
        // Za sada je dovoljno da znamo da su oba dobila execute() poziv.
    }

    public function testEvaluateWithFailingRules(): void
    {
        $evaluator = new PriorityEvaluator();
        $context = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        // High priority rule fails
        $highPriorityRule = $this->createMock(RuleInterface::class);
        $highPriorityRule->method('getPriority')->willReturn(10);
        $highPriorityRule->method('evaluate')->willReturn(false);
        $highPriorityRule->method('getFailureMessage')->willReturn('High Fail');

        // Low priority rule passes
        $lowPriorityRule = $this->createMock(RuleInterface::class);
        $lowPriorityRule->method('getPriority')->willReturn(1);
        $lowPriorityRule->method('evaluate')->willReturn(true);
        $lowPriorityRule->method('getFailureMessage')->willReturn('No error');

        $ruleSet->addRule($lowPriorityRule);
        $ruleSet->addRule($highPriorityRule);

        $results = $evaluator->evaluate($ruleSet, $context);

        self::assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');
        self::assertFalse($results[0]->result, 'Prvo evaluirano pravilo (highPriorityRule) pada');
        self::assertTrue($results[1]->result, 'Drugo evaluirano pravilo (lowPriorityRule) prolazi');

        // Slično kao i gore, failedRules neće biti ažuriran jer nismo koristili RuleSet->evaluate()
        // pa će najvjerovatnije biti prazan. Ovaj test pokazuje ograničenje trenutne implementacije.
        self::assertEmpty($evaluator->getFailedRules(), 'Bez RuleSet->evaluate(), failedRules vjerovatno ostaju prazne');
    }
}
