<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Actions;

use Maniaba\RuleEngine\Context\ContextInterface;
use Tests\Maniaba\RuleEngine\Actions\CallableActionTest;

/**
 * @see CallableActionTest
 */
final class CallableAction implements ActionInterface
{
    private readonly \Closure $callable;
    private readonly array $arguments;

    /**
     * @param callable(ContextInterface): bool $callable
     */
    public function __construct(callable $callable, ...$arguments)
    {
        $this->callable = $callable;
        $this->arguments = $arguments;
    }

    public function execute(ContextInterface $context): bool
    {
        // Poziva callable i prosljeđuje kontekst
        return ($this->callable)($context, ...$this->arguments);
    }
}
