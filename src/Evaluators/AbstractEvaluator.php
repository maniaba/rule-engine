<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;

abstract class AbstractEvaluator implements EvaluatorInterface
{
    protected array $failedRules = [];

    final public function getFailedRules(): array
    {
        return $this->failedRules;
    }

    final public function getEvaluationErrors(): EvaluatorErrors
    {
        return new EvaluatorErrors($this->failedRules);
    }

    final public function hasErrors(): bool
    {
        return [] !== $this->failedRules;
    }
}
