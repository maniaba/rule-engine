<?php

declare(strict_types=1);

namespace Tests\Support\Factories;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

final class TestAction implements ActionInterface
{
    public function __construct(
        private readonly float|int|string|null $field,
        private readonly ?string $null,
        private readonly float|int|string $value = 12,
        private readonly ?string $valueNonSigned = null,
    ) {
    }

    public function execute(ContextInterface $context): bool
    {
        // No-op for testing purposes

        return true;
    }

    public function getField(): float|int|string|null
    {
        return $this->field;
    }

    public function getNull(): ?string
    {
        return $this->null;
    }

    public function getValue(): float|int|string
    {
        return $this->value;
    }

    public function getValueNonSigned(): ?string
    {
        return $this->valueNonSigned;
    }
}
