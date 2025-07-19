<?php

namespace Tests\Support\Actions;


use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;
use function PHPUnit\Framework\assertEquals;

final class DummyArgumentsAction implements ActionInterface
{
    private array $arguments;

    /**
     * @param callable(ContextInterface): mixed $callable
     */
    public function __construct(...$arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ContextInterface $context): bool
    {
        // Obavezno postavi argumente u configu gde se koristi ovaj action
        assertEquals(['arguments' => ['arg1', 'arg2']], $this->arguments);

        return true;
    }
}
