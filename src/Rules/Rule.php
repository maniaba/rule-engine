<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Actions\AbstractAction;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * Implementation of a rule in the rule engine.
 *
 * A rule consists of a condition and two sets of actions:
 * - actions to execute when the condition is satisfied
 * - elseActions to execute when the condition is not satisfied
 *
 * @see RuleTest
 */
final class Rule implements RuleInterface
{
    /**
     * Collection of errors that occurred during action execution.
     *
     * @var list<string>
     */
    private array $executionErrors = [];

    /**
     * Cached result of the condition evaluation.
     */
    private ?bool $evaluationResult = null;

    /**
     * Creates a new Rule instance.
     *
     * @param ConditionInterface    $condition   The condition to evaluate
     * @param list<ActionInterface> $actions     Actions to execute when condition is satisfied
     * @param list<ActionInterface> $elseActions Actions to execute when condition is not satisfied
     * @param int                   $priority    The rule priority (higher number = higher priority)
     */
    public function __construct(
        private readonly ConditionInterface $condition,
        private readonly array $actions = [],
        private readonly array $elseActions = [],
        private int $priority = 0,
    ) {
    }

    /**
     * Executes actions based on the evaluation result.
     *
     * If the condition is satisfied, executes the primary actions.
     * Otherwise, executes the else actions.
     *
     * @param ContextInterface $context The context data to use for execution
     */
    public function execute(ContextInterface $context): void
    {
        $result = $this->evaluationResult ??= $this->condition->isSatisfied($context);

        if ($result) {
            foreach ($this->actions as $action) {
                if (! $action->execute($context)) {
                    $this->collectActionErrors($action);
                }
            }
        } else {
            foreach ($this->elseActions as $action) {
                if (! $action->execute($context)) {
                    $this->collectActionErrors($action);
                }
            }
        }
    }

    /**
     * Gets the failure message from the condition when evaluation fails.
     *
     * @return list<string>|string|null The failure message(s) or null if no failure occurred
     */
    public function getFailureMessage(): array|string|null
    {
        return $this->condition->getFailureMessage();
    }

    /**
     * Evaluates the rule based on the provided context.
     *
     * @param ContextInterface $context The context data to evaluate against
     *
     * @return bool True if the rule's condition is satisfied, false otherwise
     */
    public function evaluate(ContextInterface $context): bool
    {
        return $this->evaluationResult ??= $this->condition->isSatisfied($context);
    }

    /**
     * Gets the priority of the rule.
     *
     * @return int The rule priority (higher number = higher priority)
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Sets the priority of the rule.
     *
     * @param int $priority The new priority value
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Gets any errors that occurred during rule execution.
     *
     * @return list<string>|null The execution errors or null if no errors occurred
     */
    public function getExecutionErrors(): ?array
    {
        return [] !== $this->executionErrors ? $this->executionErrors : null;
    }

    /**
     * Collects error messages from an action that failed to execute.
     *
     * @param ActionInterface $action The action that failed
     */
    private function collectActionErrors(ActionInterface $action): void
    {
        if ($action instanceof AbstractAction) {
            $errors = $action->getFailureMessage();

            if (null !== $errors) {
                $errors                = \is_array($errors) ? $errors : [$errors];
                $this->executionErrors = array_merge($this->executionErrors, $errors);
            }
        }
    }
}
