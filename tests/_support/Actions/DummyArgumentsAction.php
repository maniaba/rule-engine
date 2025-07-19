<?php

declare(strict_types=1);

namespace Tests\Support\Actions;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

use function PHPUnit\Framework\assertEquals;

final class DummyArgumentsAction implements ActionInterface
{
    private readonly array $arguments;

    public function __construct(...$arguments)
    {
        $this->arguments = $arguments;
    }

    public function execute(ContextInterface $context): bool
    {
        // Make sure to set arguments in the config where this action is used
        assertEquals(['arguments' => ['arg1', 'arg2']], $this->arguments);

        return true;
    }
}
