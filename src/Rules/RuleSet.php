<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;

/**
 * A collection of rules that can be evaluated and executed as a group.
 *
 * RuleSet manages a list of rules and tracks which ones have failed during
 * evaluation or execution.
 */
final class RuleSet implements RuleSetInterface
{
    /**
     * List of rules in the RuleSet.
     *
     * @var list<RuleInterface>
     */
    private array $rules = [];

    /**
     * List of rules that failed during evaluation or execution.
     *
     * @var list<RuleInterface>
     */
    private array $failedRules = [];

    /**
     * Adds a rule to the RuleSet.
     *
     * @param RuleInterface $rule The rule to add to the set
     */
    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * Evaluates all rules in the RuleSet against the provided context.
     *
     * Rules that fail evaluation are added to the failedRules list.
     *
     * @param ContextInterface $context The context to evaluate rules against
     *
     * @return list<EvaluationResult> List of evaluation results for each rule
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
     * Executes all rules in the RuleSet against the provided context.
     *
     * Rules that encounter execution errors are added to the failedRules list.
     *
     * @param ContextInterface $context The context to execute rules against
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

    /**
     * Gets all rules in this RuleSet.
     *
     * @return list<RuleInterface> List of all rules
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Gets all rules that failed during evaluation or execution.
     *
     * @return list<RuleInterface> List of failed rules
     */
    public function getFailedRules(): array
    {
        return $this->failedRules;
    }
}
