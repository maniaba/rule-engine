<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Context;

interface ContextInterface
{
    public function getField(string $field): mixed;

    public function hasField(string $field): bool;
}
