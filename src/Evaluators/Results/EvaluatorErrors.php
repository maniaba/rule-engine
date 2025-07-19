<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators\Results;

use Maniaba\RuleEngine\Rules\RuleInterface;

final class EvaluatorErrors
{
    private array $errors = [
        'evaluationErrors' => [],
        'executionErrors'  => [],
    ];

    public function __construct(private readonly array $failedRules)
    {
        foreach ($this->failedRules as $rule) {
            $this->addRuleError($rule);
        }
    }

    private function addRuleError(RuleInterface $rule): void
    {
        if ($rule->getFailureMessage() !== null) {
            $ruleErrors                       = is_array($rule->getFailureMessage()) ? $rule->getFailureMessage() : [$rule->getFailureMessage()];
            $this->errors['evaluationErrors'] = array_merge($this->errors['evaluationErrors'], $ruleErrors);
        }

        if ($rule->getExecutionErrors() !== null) {
            $ruleErrors                      = is_array($rule->getExecutionErrors()) ? $rule->getExecutionErrors() : [$rule->getExecutionErrors()];
            $this->errors['executionErrors'] = array_merge($this->errors['executionErrors'], $ruleErrors);
        }
    }

    public function executionErrors(): ?array
    {
        return $this->errors['executionErrors'] ?: null;
    }

    public function allErrors(): ?array
    {
        if ($this->errors['evaluationErrors'] === [] && $this->errors['executionErrors'] === []) {
            return null;
        }

        return array_merge($this->errors['evaluationErrors'], $this->errors['executionErrors']);
    }

    public function evaluationErrors(): ?array
    {
        return $this->errors['evaluationErrors'] ?: null;
    }

    public function getFailedRules(): array
    {
        return $this->failedRules;
    }

    public function hasErrors(): bool
    {
        return $this->errors['evaluationErrors'] !== [] || $this->errors['executionErrors'] !== [];
    }
}


