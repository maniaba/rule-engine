<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;
use Tests\Maniaba\RuleEngine\Conditions\ArrayContainsConditionTest;

/**
 * @see ArrayContainsConditionTest
 */
final class ArrayContainsCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(
        private readonly string $field,
        private readonly mixed $value,
    ) {}

    protected function defaultFailureMessage(): string
    {
        return \sprintf('Field "%s" does not contain value.', $this->field);
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        // Provjera da li kontekst ima polje
        if (! $context->hasField($this->field)) {
            $this->setFailureMessage(\sprintf('Field "%s" does not exist.', $this->field));

            return false;
        }

        // Dohvaćamo vrijednost polja iz konteksta
        $array = $context->getField($this->field);

        // Provjeravamo da li je vrijednost zaista array
        if (! \is_array($array)) {
            $this->setFailureMessage(\sprintf('Field "%s" is not an array.', $this->field));

            return false;
        }

        // Provjeravamo da li array sadrži traženu vrijednost
        return \in_array($this->value, $array, true);
    }
}
