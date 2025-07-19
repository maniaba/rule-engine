<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Traits\FailureMessagesTrait;

abstract class AbstractCondition implements ConditionInterface
{
    use FailureMessagesTrait;

    protected ContextInterface $context;

    final public function isSatisfied(ContextInterface $context): bool
    {
        $this->context = $context;

        $isSatisfied = $this->evaluateCondition($context);

        if (! $isSatisfied && null === $this->failureMessage) {
            $this->setFailureMessage($this->defaultFailureMessage());
        }

        return $isSatisfied;
    }

    abstract protected function evaluateCondition(ContextInterface $context): bool;

    abstract protected function defaultFailureMessage(): string;
}
