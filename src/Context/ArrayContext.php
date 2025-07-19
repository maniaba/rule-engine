<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;

use Tests\Context\ArrayContextTest;

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
}
