<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators\Results;

use Maniaba\RuleEngine\Rules\RuleInterface;

final readonly class EvaluationResult
{
    /**
     * Konstruktor za kreiranje EvaluationResult.
     */
    public function __construct(
        public RuleInterface $rule,
        public bool $result,
        public array|string|null $failureMessage,
    ) {
    }
}


