<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Rules;

use Maniaba\RuleEngine\Context\ContextInterface;

interface RuleSetInterface
{
    /**
     * Dodaje pravilo u RuleSet.
     */
    public function addRule(RuleInterface $rule): void;

    /**
     * Evaluira sva pravila u RuleSet-u.
     *
     * @return array Lista rezultata evaluacije pravila.
     */
    public function evaluate(ContextInterface $context): array;

    /**
     * Izvršava sva pravila u RuleSet-u.
     */
    public function execute(ContextInterface $context): void;

    /**
     * Dohvaća pravila u RuleSet-u.
     *
     * @return list<RuleInterface>
     */
    public function getRules(): array;

    public function getFailedRules(): array;
}


