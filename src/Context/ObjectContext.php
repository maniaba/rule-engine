<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;

use JsonException;
use Stringable;

final class ObjectContext implements ContextInterface, Stringable
{
    public function __construct(
        private object $object,
    ) {
    }

    public function getField(string $field): mixed
    {
        return property_exists($this->object, $field) ? $this->object->{$field} : null;
    }

    public function hasField(string $field): bool
    {
        return property_exists($this->object, $field);
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function setObject(object $object): void
    {
        $this->object = $object;
    }

    public function toArray(): array
    {
        return (array) $this->object;
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
