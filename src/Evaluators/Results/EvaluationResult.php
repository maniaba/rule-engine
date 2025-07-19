<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators\Results;

use Maniaba\RuleEngine\Rules\RuleInterface;

final class EvaluationResult
{
    /**
     * Konstruktor za kreiranje EvaluationResult.
     */
    public function __construct(
        public readonly RuleInterface $rule,
        public readonly bool $result,
        public readonly array|string|null $failureMessage,
    ) {
    }
}
