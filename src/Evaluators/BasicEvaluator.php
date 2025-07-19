<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Rules\RuleSet;

/**
 * @see BasicEvaluatorTest
 */
final class BasicEvaluator extends AbstractEvaluator
{
    /**
     * Evaluira RuleSet i vraća rezultate evaluacije.
     *
     * @return list<EvaluationResult>
     */
    public function evaluate(RuleSet $ruleSet, ContextInterface $context): array
    {
        // Evaluira svako pravilo u RuleSet-u
        $results = [];

        foreach ($ruleSet->evaluate($context) as $evaluationResult) {
            $results[] = $evaluationResult;
        }

        $this->failedRules = $ruleSet->getFailedRules();

        return $results;
    }

    /**
     * Izvršava pravila u RuleSet-u na temelju evaluacije.
     */
    public function execute(RuleSet $ruleSet, ContextInterface $context): void
    {
        $ruleSet->execute($context);

        $failedRules = $ruleSet->getFailedRules();

        if ([] !== $failedRules) {
            $this->failedRules = array_merge($this->failedRules, $failedRules);
        }
    }
}
