<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;
use Tests\Evaluators\PriorityEvaluatorTest;

/**
 * @see PriorityEvaluatorTest
 */
final class PriorityEvaluator extends AbstractEvaluator
{
    public function evaluate(RuleSet $ruleSet, ContextInterface $context): array
    {
        $rules = $ruleSet->getRules();

        // Sortiramo pravila po prioritetu (veći prioritet ide prvi)
        usort($rules, static fn (RuleInterface $a, RuleInterface $b): int => $b->getPriority() <=> $a->getPriority());

        $results = [];

        foreach ($rules as $rule) {
            $result    = $rule->evaluate($context);
            $results[] = new EvaluationResult($rule, $result, $rule->getFailureMessage());
        }

        $this->failedRules = $ruleSet->getFailedRules();

        return $results;
    }

    public function execute(RuleSet $ruleSet, ContextInterface $context): void
    {
        $rules = $ruleSet->getRules();

        // Sortiramo pravila po prioritetu (veći prioritet ide prvi)
        usort($rules, static fn (RuleInterface $a, RuleInterface $b): int => $b->getPriority() <=> $a->getPriority());

        foreach ($rules as $rule) {
            $rule->execute($context);
        }
    }
}
