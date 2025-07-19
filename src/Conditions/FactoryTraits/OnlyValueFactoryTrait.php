<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions\FactoryTraits;

use Maniaba\RuleEngine\Conditions\ConditionInterface;

trait OnlyValueFactoryTrait
{
    public static function factory(array $data): ConditionInterface
    {
        if (! \array_key_exists('value', $data) && ! \array_key_exists('values', $data)) {
            throw new \InvalidArgumentException('Value is required.');
        }

        $value = $data['value'] ?? $data['values'] ?? null;

        return new self($value);
    }
}
