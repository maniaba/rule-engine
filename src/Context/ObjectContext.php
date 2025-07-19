<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;

final readonly class ObjectContext implements ContextInterface
{
    public function __construct(private object $object)
    {
    }

    public function getField(string $field): mixed
    {
        return property_exists($this->object, $field) ? $this->object->{$field} : null;
    }

    public function hasField(string $field): bool
    {
        return property_exists($this->object, $field);
    }
}


