<?php

declare(strict_types=1);

namespace Tests\Support\Factories;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

final class TestAction implements ActionInterface
{
    public function __construct(
        private readonly float|int|string|null $field,
        private ?string $nulla,
        private readonly float|int|string $value = 12,
        private ?string $valueNonSigned = null,
    ) {
    }

    public function execute(ContextInterface $context): bool
    {
        // No-op for testing purposes

        return true;
    }
}
