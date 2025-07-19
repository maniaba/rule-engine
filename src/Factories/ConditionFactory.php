<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Factories;

use InvalidArgumentException;
use Maniaba\RuleEngine\Conditions\ArrayContainsAllCondition;
use Maniaba\RuleEngine\Conditions\ArrayContainsAnyCondition;
use Maniaba\RuleEngine\Conditions\ArrayContainsCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Conditions\EndsWithCondition;
use Maniaba\RuleEngine\Conditions\EqualsCondition;
use Maniaba\RuleEngine\Conditions\GreaterThanCondition;
use Maniaba\RuleEngine\Conditions\GreaterThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\LessThanCondition;
use Maniaba\RuleEngine\Conditions\LessThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\NumericInRangeCondition;
use Maniaba\RuleEngine\Conditions\StartsWithCondition;
use Maniaba\RuleEngine\Conditions\StringContainCondition;

final class ConditionFactory
{
    /**
     * @var array<string, class-string<ConditionInterface>>
     */
    private array $conditions = [
        'equal'              => EqualsCondition::class,
        'greaterThanOrEqual' => GreaterThanOrEqualCondition::class,
        'lessThanOrEqual'    => LessThanOrEqualCondition::class,
        'contains'           => StringContainCondition::class,
        'arrayContains'      => ArrayContainsCondition::class,
        'arrayContainsAll'   => ArrayContainsAllCondition::class,
        'arrayContainsAny'   => ArrayContainsAnyCondition::class,
        'endsWith'           => EndsWithCondition::class,
        'startsWith'         => StartsWithCondition::class,
        'greaterThan'        => GreaterThanCondition::class,
        'lessThan'           => LessThanCondition::class,
        'numericInRange'     => NumericInRangeCondition::class,
    ];

    private array $customConditions = [];

    /**
     * Creates a condition instance based on the provided configuration.
     *
     * @param array $config the configuration array containing the required 'operator' and other parameters
     *
     * @return ConditionInterface the created condition instance
     *
     * @throws InvalidArgumentException if the 'operator' is missing or unsupported
     */
    public function create(array $config): ConditionInterface
    {
        $operator = $config['operator'] ?? null;

        if (null === $operator) {
            throw new InvalidArgumentException('Operator is required.');
        }

        if (! \array_key_exists($operator, $this->conditions) && ! \array_key_exists($operator, $this->customConditions)) {
            throw new InvalidArgumentException("Unsupported operator: {$operator}");
        }

        $class = $this->conditions[$operator] ?? $this->customConditions[$operator];

        return $class::factory($config);
    }

    /**
     * Unregisters a condition based on the provided operator.
     *
     * @param string $operator the operator identifying the condition to unregister
     */
    public function unregisterCondition(string $operator): void
    {
        unset($this->customConditions[$operator]);
    }

    /**
     * Resets the conditions list to its default state by reinitializing
     * it with predefined condition mappings.
     */
    public function reset(): void
    {
        $this->customConditions = [];
    }

    /**
     * Registers a list of conditions by associating operators with their respective conditions.
     *
     * @param array<string, class-string<ConditionInterface>> $conditions an associative array where keys are operators and values are condition definitions
     */
    public function registerConditions(array $conditions): void
    {
        foreach ($conditions as $operator => $condition) {
            $this->registerCondition($operator, $condition);
        }
    }

    /**
     * Registruje uslov za datog operatora sa odgovarajućom klasom uslova.
     *
     * @param string                           $operator  operator koji se registruje
     * @param class-string<ConditionInterface> $condition naziv klase koja implementira uslov
     */
    public function registerCondition(string $operator, string $condition): void
    {
        if (! class_exists($condition)) {
            throw new InvalidArgumentException('Condition class does not exist.');
        }

        if (! is_subclass_of($condition, ConditionInterface::class)) {
            throw new InvalidArgumentException('Condition must implement ' . ConditionInterface::class);
        }

        if (\array_key_exists($operator, $this->customConditions)) {
            throw new InvalidArgumentException("Operator {$operator} is already registered.");
        }

        $this->customConditions[$operator] = $condition;
    }
}
