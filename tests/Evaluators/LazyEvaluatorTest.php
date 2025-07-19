<?php

declare(strict_types=1);

namespace Tests\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\LazyEvaluator;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @see LazyEvaluator
 *
 * @internal
 */
#[Group('Others')]
final class LazyEvaluatorTest extends TestCase
{
    public function testEvaluateEmptyRuleSet(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        $results = $evaluator->evaluate($ruleSet, $context);

        $this->assertEmpty($results, 'Nema rezultata jer nema pravila');
        $this->assertEmpty($evaluator->getFailedRules(), 'Nema padnutih pravila jer ih nema');
    }

    public function testEvaluateNoPassingRules(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        // Dva pravila koja ne prolaze
        $failingRule1 = $this->createMock(RuleInterface::class);
        $failingRule1->method('evaluate')->willReturn(false);
        $failingRule1->method('getFailureMessage')->willReturn('Fail 1');

        $failingRule2 = $this->createMock(RuleInterface::class);
        $failingRule2->method('evaluate')->willReturn(false);
        $failingRule2->method('getFailureMessage')->willReturn('Fail 2');

        $ruleSet->addRule($failingRule1);
        $ruleSet->addRule($failingRule2);

        $results = $evaluator->evaluate($ruleSet, $context);

        // Treba evaluirati oba, jer nijedno ne prolazi -> nema prekidanja
        $this->assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');
        $this->assertFalse($results[0]->result);
        $this->assertFalse($results[1]->result);

        $failedRules = $evaluator->getFailedRules();
        $this->assertCount(2, $failedRules, 'Oba pravila su pala');
        $this->assertSame($failingRule1, $failedRules[0]);
        $this->assertSame($failingRule2, $failedRules[1]);
    }

    public function testEvaluateStopsOnFirstPassingRule(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        $passingRule = $this->createMock(RuleInterface::class);
        $passingRule->method('evaluate')->willReturn(true);
        $passingRule->method('getFailureMessage')->willReturn('No error');

        $failingRule = $this->createMock(RuleInterface::class);
        $failingRule->expects($this->never())->method('evaluate');

        $ruleSet->addRule($passingRule);
        $ruleSet->addRule($failingRule);

        $results = $evaluator->evaluate($ruleSet, $context);

        // Evaluacija se prekida nakon prvog pravila jer je uspješno
        $this->assertCount(1, $results, 'Treba biti samo 1 rezultat evaluacije jer se prekida nakon uspješnog pravila');
        $this->assertTrue($results[0]->result);

        $this->assertEmpty($evaluator->getFailedRules(), 'Nema padnutih pravila');
    }

    public function testEvaluateStopsAtSecondRuleIfFirstFailsAndSecondPasses(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        $failingRule = $this->createMock(RuleInterface::class);
        $failingRule->method('evaluate')->willReturn(false);
        $failingRule->method('getFailureMessage')->willReturn('Fail');

        $passingRule = $this->createMock(RuleInterface::class);
        $passingRule->method('evaluate')->willReturn(true);
        $passingRule->method('getFailureMessage')->willReturn('No error');

        $ruleSet->addRule($failingRule);
        $ruleSet->addRule($passingRule);

        $results = $evaluator->evaluate($ruleSet, $context);

        // Evaluira prvo pravilo (failing), pa drugo (passing), i onda prekida
        $this->assertCount(2, $results, 'Evaluirano je 2 pravila, jer drugo uspije i prekida proces');
        $this->assertFalse($results[0]->result);
        $this->assertTrue($results[1]->result);

        $failedRules = $evaluator->getFailedRules();
        $this->assertCount(1, $failedRules, 'Jedno pravilo je palo');
        $this->assertSame($failingRule, $failedRules[0]);
    }

    public function testExecuteStopsOnFirstPassingRule(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        // Failing rule - evaluira se, ali ne izvrsava, jer nije uspješno
        $failingRule = $this->createMock(RuleInterface::class);
        $failingRule->method('evaluate')->willReturn(false);
        $failingRule->expects($this->never())->method('execute');

        // Passing rule - evaluira se i izvrsava, nakon čega se prekida izvršenje
        $passingRule = $this->createMock(RuleInterface::class);
        $passingRule->method('evaluate')->willReturn(true);
        $passingRule->expects($this->once())->method('execute')->with($context);

        // Treći rule se nikad ne bi trebao ni evaluirati ni izvršiti
        $thirdRule = $this->createMock(RuleInterface::class);
        $thirdRule->expects($this->never())->method('evaluate');
        $thirdRule->expects($this->never())->method('execute');

        $ruleSet->addRule($failingRule);
        $ruleSet->addRule($passingRule);
        $ruleSet->addRule($thirdRule);

        $evaluator->execute($ruleSet, $context);
        // Provjeravamo da li je samo passingRule dobilo execute poziv i da je izvršenje prekinuto nakon njega.
    }

    public function testExecuteNoPassingRules(): void
    {
        $evaluator = new LazyEvaluator();
        $context   = $this->createMock(ContextInterface::class);
        $ruleSet   = new RuleSet();

        // Dva failing pravila - evaluiraju se, ali nijedno se ne izvršava
        $failingRule1 = $this->createMock(RuleInterface::class);
        $failingRule1->method('evaluate')->willReturn(false);
        $failingRule1->expects($this->never())->method('execute');

        $failingRule2 = $this->createMock(RuleInterface::class);
        $failingRule2->method('evaluate')->willReturn(false);
        $failingRule2->expects($this->never())->method('execute');

        $ruleSet->addRule($failingRule1);
        $ruleSet->addRule($failingRule2);

        // Nikad ne treba pokrenuti nijednu akciju jer nema uspješnih pravila
        $evaluator->execute($ruleSet, $context);
    }
}
