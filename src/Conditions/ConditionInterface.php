<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Conditions;

use Maniaba\RuleEngine\Context\ContextInterface;

interface ConditionInterface
{
    /**
     * Factory.
     */
    public static function factory(array $data): self;

    /**
     * Evaluira uvjet na temelju konteksta.
     *
     * @param ContextInterface $context kontekst podataka
     *
     * @return bool true ako je uvjet zadovoljen, false inače
     */
    public function isSatisfied(ContextInterface $context): bool;

    /**
     * Vraća poruku koja objašnjava zašto uslov nije zadovoljio.
     *
     * @return list<string>|string|null poruka o neuspjehu
     */
    public function getFailureMessage(): array|string|null;
}
