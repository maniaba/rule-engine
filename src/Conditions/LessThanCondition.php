<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;

final class LessThanCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(private readonly string $contextName, private readonly mixed $value)
    {
    }

    protected function defaultFailureMessage(): string
    {
        return "Field '{$this->contextName}' is not less than to the expected value '{$this->value}'.";
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        if (!$context->hasField($this->contextName)) {
            $this->setFailureMessage(sprintf('Field "%s" does not exist.', $this->contextName));

            return false;
        }

        $actualValue = $context->getField($this->contextName);

        if (!is_numeric($actualValue)) {
            $this->setFailureMessage(sprintf('Field "%s" is not comparable.', $this->contextName));

            return false;
        }

        return $actualValue < $this->value;
    }
}


