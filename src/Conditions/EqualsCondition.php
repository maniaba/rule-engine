<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;

final class EqualsCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(
        private readonly string $contextName,
        private readonly mixed $value,
    ) {}

    protected function defaultFailureMessage(): string
    {
        $value = \is_scalar($this->value) || null === $this->value ? "{$this->value}" : 'Array';

        return "Field '{$this->contextName}' does not equal the expected value '{$value}'.";
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        if (! $context->hasField($this->contextName)) {
            $this->setFailureMessage(\sprintf('Field "%s" does not exist.', $this->contextName));

            return false;
        }

        $actualValue = $context->getField($this->contextName);

        return $actualValue === $this->value;
    }
}
