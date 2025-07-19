<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use InvalidArgumentException;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

final class IfElseCondition implements ConditionInterface
{
    private array|string|null $failureMessage = null;

    public function __construct(
        private readonly ConditionInterface $ifCondition,
        private readonly ActionInterface|ConditionInterface|null $thenAction = null,
        private readonly ActionInterface|ConditionInterface|null $elseAction = null,
    ) {
    }

    public static function factory(array $data): ConditionInterface
    {
        if (! \array_key_exists('if', $data)) {
            throw new InvalidArgumentException("'if' key is missing in 'condition' node.");
        }

        if (! $data['if'] instanceof ConditionInterface) {
            throw new InvalidArgumentException("'if' must be instance of ConditionInterface.");
        }

        // then must be instance of ConditionInterface|ActionInterface
        $thenAction = $data['then'] ?? null;

        if (null !== $thenAction && (! $thenAction instanceof ConditionInterface && ! $thenAction instanceof ActionInterface)) {
            throw new InvalidArgumentException("'then' must be instance of ConditionInterface or ActionInterface.");
        }

        $elseAction = $data['else'] ?? null;

        if (null !== $elseAction && (! $elseAction instanceof ConditionInterface && ! $elseAction instanceof ActionInterface)) {
            throw new InvalidArgumentException("'else' must be instance of ConditionInterface or ActionInterface.");
        }

        return new self($data['if'], $thenAction, $elseAction);
    }

    public function execute(ContextInterface $context): void
    {
        if ($this->isSatisfied($context)) {
            $this->failureMessage = null; // No failure message if satisfied
            $this->thenAction?->execute($context);
        } elseif (null !== $this->elseAction) {
            $this->failureMessage = null; // No failure message if satisfied
            $this->elseAction->execute($context);
        }
    }

    public function isSatisfied(ContextInterface $context): bool
    {
        $result = $this->ifCondition->isSatisfied($context);

        if ($result) {
            $this->failureMessage = null;
        } elseif (null === $this->elseAction) {
            // Only set failure message if there's no else action
            $this->failureMessage = $this->ifCondition->getFailureMessage();
        } else {
            $this->failureMessage = null;
        }

        return $result;
    }

    public function getFailureMessage(): array|string|null
    {
        return $this->failureMessage;
    }
}
