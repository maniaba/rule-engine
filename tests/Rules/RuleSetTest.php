<?php

declare(strict_types=1);

namespace Tests\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class RuleSetTest extends TestCase
{
    public function testAddAndGetRules(): void
    {
        $ruleSet = new RuleSet();
        $rule    = $this->createMock(RuleInterface::class);

        $ruleSet->addRule($rule);

        $rules = $ruleSet->getRules();
        $this->assertCount(1, $rules, 'Trebao bi postojati jedan rule u RuleSet-u');
        $this->assertSame($rule, $rules[0], 'Dodan rule se ne podudara s onim dohvacenim iz RuleSet-a');
    }

    public function testEvaluateWithNoRules(): void
    {
        $ruleSet = new RuleSet();
        $context = $this->createMock(ContextInterface::class);

        $results = $ruleSet->evaluate($context);

        $this->assertEmpty($results, 'Rezultati evaluacije trebaju biti prazni jer nema pravila');
        $this->assertEmpty($ruleSet->getFailedRules(), 'Ne bi trebalo biti padnutih pravila');
    }

    public function testEvaluateWithAllPassingRules(): void
    {
        $ruleSet = new RuleSet();
        $context = $this->createMock(ContextInterface::class);

        // Kreiranje 2 pravila koja ce proci evaluaciju
        $passingRule1 = $this->createMock(RuleInterface::class);
        $passingRule1->method('evaluate')->willReturn(true);
        $passingRule1->method('getFailureMessage')->willReturn('Nema greške');

        $passingRule2 = $this->createMock(RuleInterface::class);
        $passingRule2->method('evaluate')->willReturn(true);
        $passingRule2->method('getFailureMessage')->willReturn('Nema greške');

        $ruleSet->addRule($passingRule1);
        $ruleSet->addRule($passingRule2);

        $results = $ruleSet->evaluate($context);

        $this->assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');

        foreach ($results as $result) {
            $this->assertTrue($result->result, 'Rezultat evaluacije treba biti true jer pravilo prolazi');
        }

        $this->assertEmpty($ruleSet->getFailedRules(), 'Ne bi trebalo biti padnutih pravila jer sva prolaze');
    }

    public function testEvaluateWithFailingRules(): void
    {
        $ruleSet = new RuleSet();
        $context = $this->createMock(ContextInterface::class);

        $passingRule = $this->createMock(RuleInterface::class);
        $passingRule->method('evaluate')->willReturn(true);
        $passingRule->method('getFailureMessage')->willReturn('Nema greške');

        $failingRule = $this->createMock(RuleInterface::class);
        $failingRule->method('evaluate')->willReturn(false);
        $failingRule->method('getFailureMessage')->willReturn('Greška u pravilu');

        $ruleSet->addRule($passingRule);
        $ruleSet->addRule($failingRule);

        $results = $ruleSet->evaluate($context);

        $this->assertCount(2, $results, 'Treba biti 2 rezultata evaluacije');

        // Provjera rezultata
        $this->assertTrue($results[0]->result, 'Prvi rule treba proći evaluaciju');
        $this->assertFalse($results[1]->result, 'Drugi rule treba pasti evaluaciju');

        // Provjera padnutih pravila
        $failedRules = $ruleSet->getFailedRules();
        $this->assertCount(1, $failedRules, 'Treba biti 1 palo pravilo');
        $this->assertSame($failingRule, $failedRules[0], 'Padnuto pravilo treba biti failingRule');
    }

    public function testExecuteCallsExecuteOnAllRules(): void
    {
        $ruleSet = new RuleSet();
        $context = $this->createMock(ContextInterface::class);

        $rule1 = $this->createMock(RuleInterface::class);
        $rule1->expects($this->once())->method('execute')->with($context);

        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->expects($this->once())->method('execute')->with($context);

        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);

        // Pozivamo execute i provjeravamo da li se metoda pozvala na oba pravila
        $ruleSet->execute($context);
    }
}
