<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Conditions\FactoryTraits\ContextNameValueDefaultFactoryTrait;
use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * @see ArrayContainsAnyConditionTest
 */
final class ArrayContainsAnyCondition extends AbstractCondition
{
    use ContextNameValueDefaultFactoryTrait;

    public function __construct(private readonly string $field, private readonly array $values)
    {
    }

    protected function defaultFailureMessage(): string
    {
        return sprintf('Field "%s" does not contain any of the values.', $this->field);
    }

    protected function evaluateCondition(ContextInterface $context): bool
    {
        // Provjera da li kontekst ima polje
        if (!$context->hasField($this->field)) {
            $this->setFailureMessage(sprintf('Field "%s" does not exist.', $this->field));

            return false;
        }

        // Dohvaćamo vrijednost polja iz konteksta
        $array = $context->getField($this->field);

        // Provjeravamo da li je vrijednost zaista array
        if (!is_array($array)) {
            $this->setFailureMessage(sprintf('Field "%s" is not an array.', $this->field));

            return false;
        }

        // Provjeravamo da li postoji barem jedan element
        return array_intersect($this->values, $array) !== [];
    }
}


