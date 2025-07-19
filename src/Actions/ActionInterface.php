<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Actions;

use Maniaba\RuleEngine\Context\ContextInterface;

interface ActionInterface
{
    /**
     * Izvršava akciju.
     *
     * @param ContextInterface $context Kontekst podataka.
     *
     * @return bool Rezultat akcije.
     */
    public function execute(ContextInterface $context): bool;
}


