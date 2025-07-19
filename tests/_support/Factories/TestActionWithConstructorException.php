<?php

declare(strict_types=1);

namespace Tests\Support\Factories;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

final class TestActionWithConstructorException implements ActionInterface
{
    public function execute(ContextInterface $context): bool
    {
        // No-op for testing purposes

        return true;
    }
}
