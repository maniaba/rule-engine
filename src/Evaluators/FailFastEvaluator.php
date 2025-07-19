<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleSet;

final class FailFastEvaluator extends AbstractEvaluator
{
    /**
     * {@inheritDoc}
     */
    public function execute(RuleSet $ruleSet, ContextInterface $context): void
    {
        foreach ($ruleSet->getRules() as $rule) {
            if (!$rule->evaluate($context)) {
                $this->failedRules[] = $rule;
                break;
            }

            $rule->execute($context);

            if ($rule->getExecutionErrors() !== null) {
                $this->failedRules[] = $rule;
                break;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate(RuleSet $ruleSet, ContextInterface $context): array
    {
        $results = [];

        foreach ($ruleSet->getRules() as $rule) {
            $result           = $rule->evaluate($context);
            $evaluationResult = new EvaluationResult($rule, $result, $rule->getFailureMessage());
            $results[]        = $evaluationResult;

            if (!$result) {
                $this->failedRules[] = $rule;
                break;
            }
        }

        return $results;
    }
}


