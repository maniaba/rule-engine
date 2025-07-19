<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;

final class EndsWithCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(
        private readonly string $field,
        private readonly string $suffix,
    ) {}

    protected function defaultFailureMessage(): string
    {
        return \sprintf('Field "%s" does not end with the suffix "%s".', $this->field, $this->suffix);
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
            // Field "field" does not end with the suffix ".com".
            $this->setFailureMessage(\sprintf('Field "%s" is not a valid string.', $this->field));

            return false;
        }

        // Provjera završava li string sa zadanim sufiksom
        return str_ends_with($value, $this->suffix);
    }
}
