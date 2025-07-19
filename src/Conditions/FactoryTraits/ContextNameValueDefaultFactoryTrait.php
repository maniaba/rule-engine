<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions\FactoryTraits;

use InvalidArgumentException;
use Maniaba\RuleEngine\Conditions\ConditionInterface;

trait ContextNameValueDefaultFactoryTrait
{
    public static function factory(array $data): ConditionInterface
    {
        $contextName = $data['contextName'] ?? null;
        $value       = $data['value'] ?? $data['values'] ?? null;

        if ($contextName === null) {
            throw new InvalidArgumentException('Context name is required.');
        }

        if ($value === null) {
            throw new InvalidArgumentException('Value is required.');
        }

        if (!is_string($contextName)) {
            throw new InvalidArgumentException('Context name must be a string.');
        }

        return new self($contextName, $value);
    }
}


