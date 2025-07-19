<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\RulePack;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Evaluators\EvaluatorInterface;

/**
 * Contract for rule-packs that a Workflow / RuleEngine
 * can load at boot time.
 */
interface RuleEnginePackInterface
{
    /**
     * @description Return a key-to-class map for **conditions**.
     * @example ['fieldValue' => FieldValueCondition::class]
     *
     * @return array<string, callable|class-string<ConditionInterface>>
     */
    public function conditions(): array;

    /**
     * @description Return a key-to-class|callable map for **actions**.
     * @example ['updateIssueValue' => UpdateIssueValueAction::class]
     *
     * @return array<string, callable|class-string<ActionInterface>>
     */
    public function actions(): array;

    /**
     * Provide the evaluator instance used for this pack.
     */
    public function evaluator(): EvaluatorInterface;
}


