<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Context\ContextInterface;

final class NotCondition implements ConditionInterface
{
    private null|array|string $failureMessages = null;

    /**
     * Konstruktor za negaciju.
     *
     * @param ConditionInterface $condition uslov koji će biti negiran
     */
    public function __construct(
        private readonly ConditionInterface $condition,
    ) {}

    public static function factory(array $data): ConditionInterface
    {
        if (! \array_key_exists('condition', $data)) {
            throw new \InvalidArgumentException("'condition' key is missing in 'not' node.");
        }

        if (! $data['condition'] instanceof ConditionInterface) {
            throw new \InvalidArgumentException("'condition' must be instance of ConditionInterface.");
        }

        return new self($data['condition']);
    }

    /**
     * Evaluira negaciju uslova.
     *
     * @param ContextInterface $context kontekst podataka
     *
     * @return bool negirani rezultat
     */
    public function isSatisfied(ContextInterface $context): bool
    {
        $isSatisfied = ! $this->condition->isSatisfied($context);

        $this->failureMessages = null;

        if (! $isSatisfied) {
            $failureMessage = $this->condition->getFailureMessage();

            if (\is_array($failureMessage)) {
                $this->failureMessages = array_map(
                    static fn($msg): string => "Negation of: {$msg}",
                    $failureMessage,
                );
            } elseif (\is_string($failureMessage)) {
                $this->failureMessages = "Negation of: {$failureMessage}";
            }
        }

        return $isSatisfied;
    }

    /**
     * Vraća opis neuspeha sa negacijom.
     */
    public function getFailureMessage(): null|array|string
    {
        return $this->failureMessages;
    }
}
