<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * Interface for rule objects in the rule engine.
 *
 * Rules are the core components that define conditions to evaluate and actions to execute
 * based on the evaluation results.
 */
interface RuleInterface
{
    /**
     * Evaluates the rule based on the provided context.
     *
     * @param ContextInterface $context The context data to evaluate against
     *
     * @return bool True if the rule is satisfied, false otherwise
     */
    public function evaluate(ContextInterface $context): bool;

    /**
     * Executes actions based on the evaluation result.
     *
     * @param ContextInterface $context The context data to use for execution
     */
    public function execute(ContextInterface $context): void;

    /**
     * Gets the priority of the rule.
     *
     * @return int The rule priority (higher number = higher priority)
     */
    public function getPriority(): int;

    /**
     * Gets the failure message when the rule evaluation fails.
     *
     * @return array|string|null The failure message(s) or null if no failure occurred
     */
    public function getFailureMessage(): array|string|null;

    /**
     * Gets any errors that occurred during rule execution.
     *
     * @return array|string|null The execution error(s) or null if no errors occurred
     */
    public function getExecutionErrors(): array|string|null;
}
