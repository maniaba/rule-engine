<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Actions\AbstractAction;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * Implementacija pravila.
 *
 * @see RuleTest
 *
 * @property-read list<ActionInterface> $actions
 * @property-read list<ActionInterface> $elseActions
 */
final class Rule implements RuleInterface
{
    private array $executionErrors  = [];
    private ?bool $evaluationResult = null;

    public function __construct(
        private readonly ConditionInterface $condition,
        /** @var list<ActionInterface> $actions */
        private readonly array $actions = [],
        /** @var list<ActionInterface> $elseActions */
        private readonly array $elseActions = [],
        private int $priority = 0,
    ) {
    }

    /**
     * Izvršava akcije na temelju rezultata evaluacije.
     */
    public function execute(ContextInterface $context): void
    {
        $result = $this->evaluationResult ??= $this->condition->isSatisfied($context);

        if ($result) {
            foreach ($this->actions as $action) {
                if (!$action->execute($context)) {
                    $this->collectActionErrors($action);
                }
            }
        } else {
            foreach ($this->elseActions as $action) {
                if (!$action->execute($context)) {
                    $this->collectActionErrors($action);
                }
            }
        }
    }

    /**
     * Prikuplja greške iz akcije.
     */
    private function collectActionErrors(ActionInterface $action): void
    {
        if ($action instanceof AbstractAction) {
            $errors = $action->getFailureMessage();
            if ($errors !== null) {
                $errors                = is_array($errors) ? $errors : [$errors];
                $this->executionErrors = array_merge($this->executionErrors, $errors);
            }
        }
    }

    /**
     * @return list<string>|string|null
     */
    public function getFailureMessage(): array|string|null
    {
        return $this->condition->getFailureMessage();
    }

    /**
     * Evaluira pravilo na temelju konteksta.
     */
    public function evaluate(ContextInterface $context): bool
    {
        return $this->evaluationResult ??= $this->condition->isSatisfied($context);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getExecutionErrors(): ?array
    {
        return $this->executionErrors !== [] ? $this->executionErrors : null;
    }
}


