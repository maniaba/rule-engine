<?php

declare(strict_types=1);

namespace Tests\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\BasicEvaluator;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @see BasicEvaluator
 *
 * @internal
 */
#[Group('Others')]
final class BasicEvaluatorTest extends TestCase
{
    public function testEvaluateEmptyRuleSet(): void
    {
        $evaluator = new BasicEvaluator();
        $context   = $this->createMock(ContextInterface::class);

        // Koristimo stvarni RuleSet bez ikakvih pravila
        $ruleSet = new RuleSet();

        $results = $evaluator->evaluate($ruleSet, $context);

        $this->assertEmpty($results, 'Rezultati trebaju biti prazni jer nema pravila');
        $this->assertEmpty($evaluator->getFailedRules(), 'Nema padnutih pravila jer ih nema uopšte');
    }

    public function testEvaluateAllPassingRules(): void
    {
        $evaluator = new BasicEvaluator();
        $context   = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        // Dva pravila koja uvijek prolaze evaluaciju
        $passingRule1 = $this->createMock(RuleInterface::class);
        $passingRule1->method('evaluate')->willReturn(true);
        $passingRule1->method('getFailureMessage')->willReturn('No error');

        $passingRule2 = $this->createMock(RuleInterface::class);
        $passingRule2->method('evaluate')->willReturn(true);
        $passingRule2->method('getFailureMessage')->willReturn('No error');

        $ruleSet->addRule($passingRule1);
        $ruleSet->addRule($passingRule2);

        $results = $evaluator->evaluate($ruleSet, $context);

        $this->assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');

        foreach ($results as $result) {
            $this->assertTrue($result->result, 'Rezultat treba biti true jer pravilo prolazi');
        }

        $this->assertEmpty($evaluator->getFailedRules(), 'Ne bi trebalo biti padnutih pravila');
    }

    public function testEvaluateWithSomeFailingRules(): void
    {
        $evaluator = new BasicEvaluator();
        $context   = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        $passingRule = $this->createMock(RuleInterface::class);
        $passingRule->method('evaluate')->willReturn(true);
        $passingRule->method('getFailureMessage')->willReturn('No error');

        $failingRule = $this->createMock(RuleInterface::class);
        $failingRule->method('evaluate')->willReturn(false);
        $failingRule->method('getFailureMessage')->willReturn('Failed rule');

        $ruleSet->addRule($passingRule);
        $ruleSet->addRule($failingRule);

        $results = $evaluator->evaluate($ruleSet, $context);

        $this->assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');
        $this->assertTrue($results[0]->result, 'Prvo pravilo treba proći');
        $this->assertFalse($results[1]->result, 'Drugo pravilo treba pasti');

        $failedRules = $evaluator->getFailedRules();
        $this->assertCount(1, $failedRules, 'Treba biti 1 palo pravilo');
        $this->assertSame($failingRule, $failedRules[0], 'Palo pravilo se ne poklapa');
    }

    public function testExecuteCallsExecuteOnRuleSetRules(): void
    {
        $evaluator = new BasicEvaluator();
        $context   = $this->createMock(ContextInterface::class);

        $ruleSet = new RuleSet();

        // Dva pravila, treba provjeriti da li se execute poziva na oba
        $rule1 = $this->createMock(RuleInterface::class);
        $rule1->expects($this->once())->method('execute')->with($context);

        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->expects($this->once())->method('execute')->with($context);

        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);

        // Pozovemo execute evaluator i provjerimo da li se pozvao execute ruleSet-a
        $evaluator->execute($ruleSet, $context);
        // Ako se test ne "buni", znači da je execute pozvan očekivani broj puta (once) na svakom pravilu.
    }
}
