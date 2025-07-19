<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * Interface for rule set objects in the rule engine.
 *
 * A rule set is a collection of rules that can be evaluated and executed as a group.
 */
interface RuleSetInterface
{
    /**
     * Adds a rule to the rule set.
     *
     * @param RuleInterface $rule The rule to add to the set
     */
    public function addRule(RuleInterface $rule): void;

    /**
     * Evaluates all rules in the rule set against the provided context.
     *
     * @param ContextInterface $context The context to evaluate rules against
     *
     * @return array List of evaluation results for each rule
     */
    public function evaluate(ContextInterface $context): array;

    /**
     * Executes all rules in the rule set against the provided context.
     *
     * @param ContextInterface $context The context to execute rules against
     */
    public function execute(ContextInterface $context): void;

    /**
     * Gets all rules in this rule set.
     *
     * @return list<RuleInterface> List of all rules
     */
    public function getRules(): array;

    /**
     * Gets all rules that failed during evaluation or execution.
     *
     * @return list<RuleInterface> List of failed rules
     */
    public function getFailedRules(): array;
}
