<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;

final class StringContainCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(
        private readonly string $field,
        private readonly string $needle,
    ) {
    }

    protected function defaultFailureMessage(): string
    {
        return \sprintf('String does not contain the substring "%s".', $this->needle);
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        if (! $context->hasField($this->field)) {
            $this->setFailureMessage(\sprintf('Field "%s" does not exist.', $this->field));

            return false;
        }

        // Dohvaćamo vrijednost iz konteksta
        $value = $context->getField($this->field);

        // Provjera je li vrijednost string
        if (! \is_string($value)) {
            $this->setFailureMessage(\sprintf('Field "%s" is not a valid string.', $this->field));

            return false;
        }

        // Provjera sadrži li string zadani podstring
        return str_contains($value, $this->needle);
    }
}
