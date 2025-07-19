<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Evaluators;

use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluationResult;
use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;
use Maniaba\RuleEngine\Rules\RuleInterface;
use Maniaba\RuleEngine\Rules\RuleSet;

interface EvaluatorInterface
{
    /**
     * Evaluira skup pravila u RuleSet-u.
     *
     * @param RuleSet          $ruleSet skup pravila za evaluaciju
     * @param ContextInterface $context kontekst podataka
     *
     * @return list<EvaluationResult> lista rezultata evaluacije
     */
    public function evaluate(RuleSet $ruleSet, ContextInterface $context): array;

    /**
     * Izvršava skup pravila u RuleSet-u.
     *
     * @param RuleSet          $ruleSet skup pravila za izvršenje
     * @param ContextInterface $context kontekst podataka
     */
    public function execute(RuleSet $ruleSet, ContextInterface $context): void;

    /**
     * @return list<RuleInterface> lista pravila koja nisu prošla evaluaciju
     */
    public function getFailedRules(): array;

    public function getEvaluationErrors(): EvaluatorErrors;

    public function hasErrors(): bool;
}
