<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\RulePack;

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Builders\JsonBuilder;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Evaluators\EvaluatorInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;
use Maniaba\RuleEngine\Rules\RuleSet;

abstract class AbstractRuleEnginePack implements RuleEnginePackInterface
{
    private EvaluatorInterface $evaluator;

    public function getErrors(): EvaluatorErrors
    {
        return $this->evaluator->getEvaluationErrors();
    }

    public function execute(array|string $config, ContextInterface $context): bool
    {
        if ($config === [] || $config === '') {
            return true; // No rules to evaluate
        }

        $ruleSet = $this->builder($config);
        $this->evaluator()->evaluate($ruleSet, $context);

        if ($this->evaluator()->hasErrors()) {
            return false;
        }

        // Execute actions
        $this->evaluator()->execute($ruleSet, $context);

        return !$this->evaluator()->hasErrors();
    }

    // execute

    public function builder(array|string $config): RuleSet
    {
        $builder = is_string($config) ? new JsonBuilder() : new ArrayBuilder();

        $builder->conditions()->registerConditions($this->conditions());
        $builder->actions()->registerActions($this->actions());

        return $builder->build($config);
    }

    public function evaluate(array|string $config, ContextInterface $context): bool
    {
        if ($config === [] || $config === '') {
            return true; // No rules to evaluate
        }

        $ruleSet = $this->builder($config);
        $this->evaluator()->evaluate($ruleSet, $context);

        return !$this->evaluator()->hasErrors();
    }

    public function evaluator(): EvaluatorInterface
    {
        if (!isset($this->evaluator)) {
            $evaluatorClass  = $this->evaluatorClass();
            $this->evaluator = new $evaluatorClass();
        }

        return $this->evaluator;
    }

    /**
     * @return class-string<EvaluatorInterface>
     */
    abstract protected function evaluatorClass(): string;
}


