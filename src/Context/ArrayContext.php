<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;


/**
 * @see ArrayContextTest
 */
final class ArrayContext implements ContextInterface
{
    public function __construct(
        private array $data,
    ) {}

    public function getField(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    public function hasField(string $field): bool
    {
        return \array_key_exists($field, $this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setField(string|int|float $field, mixed $value): void
    {
        $this->data[$field] = $value;
    }

    public function removeField(string $field): void
    {
        unset($this->data[$field]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function __toString(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function merge(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function getKeys(): array
    {
        return array_keys($this->data);
    }

    public function getValues(): array
    {
        return array_values($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    public function filter(callable $callback): array
    {
        return array_filter($this->data, $callback);
    }

}
