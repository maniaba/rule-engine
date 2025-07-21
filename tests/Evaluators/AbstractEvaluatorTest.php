<?php

declare(strict_types=1);

namespace Tests\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\AbstractEvaluator;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AbstractEvaluatorTest extends TestCase
{
    private function getMockEvaluator(array $failedRules = []): AbstractEvaluator
    {
        return new class ($failedRules) extends AbstractEvaluator {
            public function __construct(array $failedRules)
            {
                $this->failedRules = $failedRules;
            }

            public function evaluate(RuleSet $ruleSet, ContextInterface $context): array
            {
                return [];
            }

            public function execute(RuleSet $ruleSet, ContextInterface $context): void
            {
            }
        };
    }

    /**
     * @throws Exception
     */
    public function testGetFailedRulesReturnsFailedRules(): void
    {
        $mockRule1 = $this->createMock(RuleInterface::class);
        $mockRule2 = $this->createMock(RuleInterface::class);
        $failed    = [$mockRule1, $mockRule2];
        $evaluator = $this->getMockEvaluator($failed);
        $this->assertSame($failed, $evaluator->getFailedRules());
    }

    /**
     * @throws Exception
     */
    public function testHasErrorsReturnsTrueIfFailedRulesNotEmpty(): void
    {
        $mockRule  = $this->createMock(RuleInterface::class);
        $evaluator = $this->getMockEvaluator([$mockRule]);
        $this->assertTrue($evaluator->hasErrors());
    }

    public function testHasErrorsReturnsFalseIfFailedRulesEmpty(): void
    {
        $evaluator = $this->getMockEvaluator([]);
        $this->assertFalse($evaluator->hasErrors());
    }
}
