<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Context\ContextInterface;

final class AlwaysTrueCondition extends AbstractCondition
{
    public static function factory(array $data): ConditionInterface
    {
        return new self();
    }

    protected function defaultFailureMessage(): string
    {
        return '';
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        return true;
    }
}
