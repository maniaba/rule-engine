<?php

declare(strict_types=1);

namespace Tests\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\FailFastEvaluator;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FailFastEvaluatorTest extends TestCase
{
    private FailFastEvaluator $evaluator;
    private ContextInterface $context;

    protected function setUp(): void
    {
        $this->evaluator = new FailFastEvaluator();
        $this->context   = $this->createMock(ContextInterface::class);
    }

    public function testExecuteAllRulesPassWithoutExecutionErrors(): void
    {
        $rule1 = $this->createMockRule(true, null);
        $rule2 = $this->createMockRule(true, null);
        $rule3 = $this->createMockRule(true, null);

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);
        $ruleSet->addRule($rule3);

        $this->evaluator->execute($ruleSet, $this->context);

        $this->assertEmpty($this->evaluator->getFailedRules());
        $this->assertFalse($this->evaluator->hasErrors());
    }

    public function testExecuteStopsOnFirstFailedEvaluation(): void
    {
        $rule1 = $this->createMockRule(true, null);
        $rule2 = $this->createMockRule(false, 'Rule 2 failed');
        $rule3 = $this->createMockRule(true, null, false); // Should not be called

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);
        $ruleSet->addRule($rule3);

        $this->evaluator->execute($ruleSet, $this->context);

        $failedRules = $this->evaluator->getFailedRules();
        $this->assertCount(1, $failedRules);
        $this->assertSame($rule2, $failedRules[0]);
        $this->assertTrue($this->evaluator->hasErrors());
    }

    public function testExecuteStopsOnFirstExecutionError(): void
    {
        $rule1 = $this->createMockRule(true, null);
        $rule2 = $this->createMockRule(true, null, true, 'Execution error');
        $rule3 = $this->createMockRule(true, null, false); // Should not be called

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);
        $ruleSet->addRule($rule3);

        $this->evaluator->execute($ruleSet, $this->context);

        $failedRules = $this->evaluator->getFailedRules();
        $this->assertCount(1, $failedRules);
        $this->assertSame($rule2, $failedRules[0]);
        $this->assertTrue($this->evaluator->hasErrors());
    }

    public function testEvaluateAllRulesPass(): void
    {
        $rule1 = $this->createMockRule(true, null, true, null, true);
        $rule2 = $this->createMockRule(true, null, true, null, true);
        $rule3 = $this->createMockRule(true, null, true, null, true);

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);
        $ruleSet->addRule($rule3);

        $results = $this->evaluator->evaluate($ruleSet, $this->context);

        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(EvaluationResult::class, $results);

        foreach ($results as $result) {
            $this->assertTrue($result->result);
        }

        $this->assertEmpty($this->evaluator->getFailedRules());
        $this->assertFalse($this->evaluator->hasErrors());
    }

    public function testEvaluateStopsOnFirstFailure(): void
    {
        $rule1 = $this->createMockRule(true, null, true, null, true);
        $rule2 = $this->createMockRule(false, 'Rule 2 failed', true, null, true);
        $rule3 = $this->createMockRule(true, null, false, null, true);

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);
        $ruleSet->addRule($rule3);

        $results = $this->evaluator->evaluate($ruleSet, $this->context);

        $this->assertCount(2, $results);
        $this->assertTrue($results[0]->result);
        $this->assertFalse($results[1]->result);

        $failedRules = $this->evaluator->getFailedRules();
        $this->assertCount(1, $failedRules);
        $this->assertSame($rule2, $failedRules[0]);
        $this->assertTrue($this->evaluator->hasErrors());
    }

    public function testEvaluateFirstRuleFails(): void
    {
        $rule1 = $this->createMockRule(false, 'First rule failed', true, null, true);
        $rule2 = $this->createMockRule(true, null, false, null, true);

        $ruleSet = new RuleSet();
        $ruleSet->addRule($rule1);
        $ruleSet->addRule($rule2);

        $results = $this->evaluator->evaluate($ruleSet, $this->context);

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]->result);

        $failedRules = $this->evaluator->getFailedRules();
        $this->assertCount(1, $failedRules);
        $this->assertSame($rule1, $failedRules[0]);
        $this->assertTrue($this->evaluator->hasErrors());
    }

    public function testEvaluateEmptyRuleSet(): void
    {
        $ruleSet = new RuleSet();

        $results = $this->evaluator->evaluate($ruleSet, $this->context);

        $this->assertEmpty($results);
        $this->assertEmpty($this->evaluator->getFailedRules());
        $this->assertFalse($this->evaluator->hasErrors());
    }

    public function testExecuteEmptyRuleSet(): void
    {
        $ruleSet = new RuleSet();

        $this->evaluator->execute($ruleSet, $this->context);

        $this->assertEmpty($this->evaluator->getFailedRules());
        $this->assertFalse($this->evaluator->hasErrors());
    }

    private function createMockRule(
        bool $evaluationResult,
        ?string $failureMessage = null,
        bool $shouldBeCalled = true,
        ?string $executionError = null,
        bool $skipExecutionMock = false,
    ): RuleInterface {
        $rule = $this->createMock(RuleInterface::class);

        if ($shouldBeCalled) {
            $rule->expects($this->once())
                ->method('evaluate')
                ->with($this->context)
                ->willReturn($evaluationResult);

            $rule
                ->method('getFailureMessage')
                ->willReturn($failureMessage);

            if ($evaluationResult && ! $skipExecutionMock) {
                $rule->expects($this->once())
                    ->method('execute')
                    ->with($this->context);

                $rule->expects($this->once())
                    ->method('getExecutionErrors')
                    ->willReturn($executionError);
            } else {
                $rule->expects($this->never())->method('execute');
                $rule->expects($this->never())->method('getExecutionErrors');
            }
        } else {
            $rule->expects($this->never())->method('evaluate');
            $rule->expects($this->never())->method('execute');
            $rule->expects($this->never())->method('getExecutionErrors');
        }

        return $rule;
    }
}
