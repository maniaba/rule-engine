<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleSet;

final class LazyEvaluator extends AbstractEvaluator
{
    public function execute(RuleSet $ruleSet, ContextInterface $context): void
    {
        foreach ($ruleSet->getRules() as $rule) {
            if ($rule->evaluate($context)) {
                $rule->execute($context);
                break;
            }
        }
    }

    public function evaluate(RuleSet $ruleSet, ContextInterface $context): array
    {
        $results     = [];
        $failedRules = [];

        foreach ($ruleSet->getRules() as $rule) {
            $result           = $rule->evaluate($context);
            $evaluationResult = new EvaluationResult($rule, $result, $rule->getFailureMessage());
            $results[]        = $evaluationResult;

            if (! $result) {
                $failedRules[] = $rule;
            } else {
                // Ako je rule uspješan, prekidamo evaluaciju
                break;
            }
        }

        $this->failedRules = $failedRules;

        return $results;
    }
}
