<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use InvalidArgumentException;
use Maniaba\RuleEngine\Context\ContextInterface;

final class NumericInRangeCondition extends AbstractCondition
{
    /**
     * Konstruktor.
     *
     * @param string    $contextName Naziv polja iz konteksta.
     * @param float|int $min         Minimalna dozvoljena vrednost.
     * @param float|int $max         Maksimalna dozvoljena vrednost.
     */
    public function __construct(private readonly string $contextName, private readonly float|int $min, private readonly float|int $max)
    {
    }

    public static function factory(array $data): ConditionInterface
    {
        if (!isset($data['min']) || !isset($data['max']) || !is_numeric($data['min']) || !is_numeric($data['max'])) {
            throw new InvalidArgumentException('Min and max values are required.');
        }
        $contextName = $data['contextName'] ?? null;

        if ($contextName === null) {
            throw new InvalidArgumentException('Context name is required.');
        }

        return new NumericInRangeCondition($contextName, $data['min'], $data['max']);
    }

    /**
     * Vraća poruku o neuspehu.
     */
    protected function defaultFailureMessage(): string
    {
        return sprintf(
            "Field '%s' must be between %.2f and %.2f.",
            $this->contextName,
            $this->min,
            $this->max,
        );
    }

    /**
     * Proverava da li je vrednost unutar opsega.
     *
     * @param ContextInterface $context Kontekst podataka.
     *
     * @return bool True ako je vrednost unutar opsega, false inače.
     */
    protected function evaluateCondition(ContextInterface $context): bool
    {
        if (!$context->hasField($this->contextName)) {
            $this->setFailureMessage(sprintf('Field "%s" does not exist.', $this->contextName));

            return false;
        }

        $value = $context->getField($this->contextName);

        if (!is_numeric($value)) {
            $this->setFailureMessage(sprintf('Field "%s" is not numeric.', $this->contextName));

            return false;
        }

        return $value >= $this->min && $value <= $this->max;
    }
}


