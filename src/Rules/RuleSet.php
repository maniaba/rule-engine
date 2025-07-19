<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;

final class RuleSet implements RuleSetInterface
{
    /**
     * Lista pravila u RuleSet-u.
     *
     * @var list<RuleInterface>
     */
    private array $rules = [];

    private array $failedRules = [];

    /**
     * Dodaje pravilo u RuleSet.
     */
    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Evaluira sva pravila u RuleSet-u.
     *
     * @return list<EvaluationResult> lista rezultata evaluacije pravila
     */
    public function evaluate(ContextInterface $context): array
    {
        $results = [];

        foreach ($this->rules as $rule) {
            $result    = $rule->evaluate($context);
            $results[] = new EvaluationResult($rule, $result, $rule->getFailureMessage());

            if (! $result) {
                $this->failedRules[] = $rule;
            }
        }

        return $results;
    }

    /**
     * Izvršava sva pravila u RuleSet-u.
     */
    public function execute(ContextInterface $context): void
    {
        foreach ($this->rules as $rule) {
            $rule->execute($context);

            if ($rule->getExecutionErrors() !== null) {
                $this->failedRules[] = $rule;
            }
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getFailedRules(): array
    {
        return $this->failedRules;
    }
}
