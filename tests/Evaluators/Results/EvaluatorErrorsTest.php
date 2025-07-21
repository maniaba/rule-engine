<?php

declare(strict_types=1);

namespace Tests\Evaluators\Results;

use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;
use Maniaba\RuleEngine\Rules\RuleInterface;
use PHPUnit\Framework\TestCase;

/**
 * @see EvaluatorErrors
 *
 * @internal
 */
final class EvaluatorErrorsTest extends TestCase
{
    public function testConstructorWithEmptyFailedRules(): void
    {
        $evaluatorErrors = new EvaluatorErrors([]);

        $this->assertEmpty($evaluatorErrors->getFailedRules());
        $this->assertFalse($evaluatorErrors->hasErrors());
        $this->assertNull($evaluatorErrors->allErrors());
        $this->assertNull($evaluatorErrors->evaluationErrors());
        $this->assertNull($evaluatorErrors->executionErrors());
    }

    public function testConstructorWithRuleHavingStringFailureMessage(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule
            ->method('getFailureMessage')
            ->willReturn('Test failure message');
        $rule
            ->method('getExecutionErrors')
            ->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertCount(1, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());
        $this->assertSame(['Test failure message'], $evaluatorErrors->evaluationErrors());
        $this->assertNull($evaluatorErrors->executionErrors());
        $this->assertSame(['Test failure message'], $evaluatorErrors->allErrors());
    }

    public function testConstructorWithRuleHavingArrayFailureMessage(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule
            ->method('getFailureMessage')
            ->willReturn(['Error 1', 'Error 2']);
        $rule
            ->method('getExecutionErrors')
            ->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertCount(1, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());
        $this->assertSame(['Error 1', 'Error 2'], $evaluatorErrors->evaluationErrors());
        $this->assertNull($evaluatorErrors->executionErrors());
        $this->assertSame(['Error 1', 'Error 2'], $evaluatorErrors->allErrors());
    }

    public function testConstructorWithRuleHavingStringExecutionError(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule
            ->method('getFailureMessage')
            ->willReturn(null);
        $rule
            ->method('getExecutionErrors')
            ->willReturn('Test execution error');

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertCount(1, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());
        $this->assertNull($evaluatorErrors->evaluationErrors());
        $this->assertSame(['Test execution error'], $evaluatorErrors->executionErrors());
        $this->assertSame(['Test execution error'], $evaluatorErrors->allErrors());
    }

    public function testConstructorWithRuleHavingArrayExecutionError(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule
            ->method('getFailureMessage')
            ->willReturn(null);
        $rule
            ->method('getExecutionErrors')
            ->willReturn(['Execution Error 1', 'Execution Error 2']);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertCount(1, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());
        $this->assertNull($evaluatorErrors->evaluationErrors());
        $this->assertSame(['Execution Error 1', 'Execution Error 2'], $evaluatorErrors->executionErrors());
        $this->assertSame(['Execution Error 1', 'Execution Error 2'], $evaluatorErrors->allErrors());
    }

    public function testConstructorWithRuleHavingBothFailureAndExecutionErrors(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule
            ->method('getFailureMessage')
            ->willReturn('Evaluation error');
        $rule
            ->method('getExecutionErrors')
            ->willReturn('Execution error');

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertCount(1, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());
        $this->assertSame(['Evaluation error'], $evaluatorErrors->evaluationErrors());
        $this->assertSame(['Execution error'], $evaluatorErrors->executionErrors());
        $this->assertSame(['Evaluation error', 'Execution error'], $evaluatorErrors->allErrors());
    }

    public function testConstructorWithMultipleRules(): void
    {
        $rule1 = $this->createMock(RuleInterface::class);
        $rule1
            ->method('getFailureMessage')
            ->willReturn(['Rule 1 Error 1', 'Rule 1 Error 2']);
        $rule1
            ->method('getExecutionErrors')
            ->willReturn('Rule 1 Execution Error');

        $rule2 = $this->createMock(RuleInterface::class);
        $rule2
            ->method('getFailureMessage')
            ->willReturn('Rule 2 Error');
        $rule2
            ->method('getExecutionErrors')
            ->willReturn(['Rule 2 Execution Error 1', 'Rule 2 Execution Error 2']);

        $rule3 = $this->createMock(RuleInterface::class);
        $rule3
            ->method('getFailureMessage')
            ->willReturn(null);
        $rule3
            ->method('getExecutionErrors')
            ->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule1, $rule2, $rule3]);

        $this->assertCount(3, $evaluatorErrors->getFailedRules());
        $this->assertTrue($evaluatorErrors->hasErrors());

        $expectedEvaluationErrors = ['Rule 1 Error 1', 'Rule 1 Error 2', 'Rule 2 Error'];
        $expectedExecutionErrors  = ['Rule 1 Execution Error', 'Rule 2 Execution Error 1', 'Rule 2 Execution Error 2'];

        $this->assertSame($expectedEvaluationErrors, $evaluatorErrors->evaluationErrors());
        $this->assertSame($expectedExecutionErrors, $evaluatorErrors->executionErrors());
        $this->assertSame(array_merge($expectedEvaluationErrors, $expectedExecutionErrors), $evaluatorErrors->allErrors());
    }

    public function testGetFailedRules(): void
    {
        $rule1 = $this->createMock(RuleInterface::class);
        $rule1->method('getFailureMessage')->willReturn(null);
        $rule1->method('getExecutionErrors')->willReturn(null);

        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->method('getFailureMessage')->willReturn(null);
        $rule2->method('getExecutionErrors')->willReturn(null);

        $rules           = [$rule1, $rule2];
        $evaluatorErrors = new EvaluatorErrors($rules);

        $this->assertSame($rules, $evaluatorErrors->getFailedRules());
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(null);
        $rule->method('getExecutionErrors')->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertFalse($evaluatorErrors->hasErrors());
    }

    public function testHasErrorsReturnsTrueWhenEvaluationErrorsExist(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn('Error');
        $rule->method('getExecutionErrors')->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertTrue($evaluatorErrors->hasErrors());
    }

    public function testHasErrorsReturnsTrueWhenExecutionErrorsExist(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(null);
        $rule->method('getExecutionErrors')->willReturn('Error');

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertTrue($evaluatorErrors->hasErrors());
    }

    public function testEvaluationErrorsReturnsNullWhenEmpty(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(null);
        $rule->method('getExecutionErrors')->willReturn('Execution error');

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertNull($evaluatorErrors->evaluationErrors());
    }

    public function testExecutionErrorsReturnsNullWhenEmpty(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn('Evaluation error');
        $rule->method('getExecutionErrors')->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertNull($evaluatorErrors->executionErrors());
    }

    public function testAllErrorsReturnsNullWhenNoErrors(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(null);
        $rule->method('getExecutionErrors')->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertNull($evaluatorErrors->allErrors());
    }

    public function testAllErrorsCombinesEvaluationAndExecutionErrors(): void
    {
        $rule1 = $this->createMock(RuleInterface::class);
        $rule1->method('getFailureMessage')->willReturn(['Eval Error 1', 'Eval Error 2']);
        $rule1->method('getExecutionErrors')->willReturn(['Exec Error 1']);

        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->method('getFailureMessage')->willReturn('Eval Error 3');
        $rule2->method('getExecutionErrors')->willReturn(['Exec Error 2', 'Exec Error 3']);

        $evaluatorErrors = new EvaluatorErrors([$rule1, $rule2]);

        $expectedAllErrors = [
            'Eval Error 1',
            'Eval Error 2',
            'Eval Error 3',
            'Exec Error 1',
            'Exec Error 2',
            'Exec Error 3',
        ];

        $this->assertSame($expectedAllErrors, $evaluatorErrors->allErrors());
    }

    public function testAllErrorsReturnsOnlyEvaluationErrorsWhenExecutionErrorsEmpty(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(['Error 1', 'Error 2']);
        $rule->method('getExecutionErrors')->willReturn(null);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertSame(['Error 1', 'Error 2'], $evaluatorErrors->allErrors());
    }

    public function testAllErrorsReturnsOnlyExecutionErrorsWhenEvaluationErrorsEmpty(): void
    {
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getFailureMessage')->willReturn(null);
        $rule->method('getExecutionErrors')->willReturn(['Error 1', 'Error 2']);

        $evaluatorErrors = new EvaluatorErrors([$rule]);

        $this->assertSame(['Error 1', 'Error 2'], $evaluatorErrors->allErrors());
    }
}
