<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use InvalidArgumentException;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Enums\CollectionConditionType;

final class CollectionCondition implements ConditionInterface
{
    private array $failureMessages = [];

    /**
     * @param list<ConditionInterface> $conditions
     */
    public function __construct(public readonly CollectionConditionType $type, private readonly array $conditions)
    {
    }

    public static function factory(array $data): ConditionInterface
    {
        foreach (['type', 'nodes'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("'{$key}' key is missing in 'collection' node.");
            }
        }

        $type = CollectionConditionType::tryFrom($data['type']);

        if ($type === null) {
            throw new InvalidArgumentException("Invalid collection condition type: {$data['type']}");
        }

        if (!is_array($data['nodes'])) {
            throw new InvalidArgumentException('Nodes must be an array.');
        }

        // check $data[nodes] must be all instances ConditionInterface
        foreach ($data['nodes'] as $node) {
            if (!$node instanceof ConditionInterface) {
                throw new InvalidArgumentException('Condition must implement ConditionInterface.');
            }
        }

        return new CollectionCondition($type, $data['nodes']);
    }

    public function isSatisfied(ContextInterface $context): bool
    {
        $this->failureMessages = []; // Reset failure messages
        $atLeastOnePassed      = false;

        foreach ($this->conditions as $condition) {
            if (!$condition instanceof ConditionInterface) {
                throw new InvalidArgumentException('Condition must implement ConditionInterface.');
            }

            $result = $condition->isSatisfied($context);

            if ($result) {
                $atLeastOnePassed = true;
            } else {
                $failureMessage = $condition->getFailureMessage();
                if (is_array($failureMessage)) {
                    $this->failureMessages = array_merge($this->failureMessages, $failureMessage);
                } elseif ($failureMessage !== '' && $failureMessage !== '0' && $failureMessage !== []) {
                    $this->failureMessages[] = $failureMessage;
                }
            }

            // Odmah vrati true za OR ako je jedan uslov prošao
            if ($this->type === CollectionConditionType::OR && $result) {
                $this->failureMessages = [];

                return true;
            }
        }

        // Ako je AND, sve mora proći, proveri neuspeh
        if ($this->type === CollectionConditionType::AND) {
            return $this->failureMessages === [];
        }

        // Ako je OR, ali nijedan nije prošao
        return $atLeastOnePassed;
    }

    /**
     * Returns the failure messages of conditions that failed.
     *
     * @return array|null An array of failure messages or null if no failure.
     */
    public function getFailureMessage(): ?array
    {
        return $this->failureMessages === [] ? null : $this->failureMessages;
    }
}


