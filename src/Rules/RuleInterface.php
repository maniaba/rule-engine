<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;

interface RuleInterface
{
    /**
     * Evaluira pravilo na temelju konteksta.
     *
     * @param ContextInterface $context kontekst podataka
     *
     * @return bool true ako je pravilo zadovoljeno, false inače
     */
    public function evaluate(ContextInterface $context): bool;

    /**
     * Izvršava akcije na temelju rezultata evaluacije.
     *
     * @param ContextInterface $context kontekst podataka
     */
    public function execute(ContextInterface $context): void;

    /**
     * Dohvata prioritet pravila.
     *
     * @return int prioritet pravila (veći broj = veći prioritet)
     */
    public function getPriority(): int;

    public function getFailureMessage(): array|string|null;

    public function getExecutionErrors(): array|string|null;
}
