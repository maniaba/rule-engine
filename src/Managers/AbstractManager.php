<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Managers;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Builders\JsonBuilder;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Evaluators\EvaluatorInterface;
use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;
use Maniaba\RuleEngine\Rules\RuleSet;

abstract class AbstractManager implements WorkflowManagerInterface
{
    private EvaluatorInterface $evaluator;

    final public function builder(array|string $config): RuleSet
    {
        $builder = \is_string($config) ? new JsonBuilder() : new ArrayBuilder();

        $builder->conditions()->registerConditions($this->registerConditions());
        $builder->actions()->registerActions($this->registerActions());

        return $builder->build($config);
    }

    final public function getErrors(): EvaluatorErrors
    {
        return $this->evaluator->getEvaluationErrors();
    }

    final public function evaluator(): EvaluatorInterface
    {
        if (! isset($this->evaluator)) {
            $evaluatorClass = $this->evaluatorClass();
            $this->evaluator = new $evaluatorClass();
        }

        return $this->evaluator;
    }

    /**
     * @return array<string, class-string<ConditionInterface>>
     */
    abstract protected function registerConditions(): array;

    /**
     * @return array<string, class-string<ActionInterface>>
     */
    abstract protected function registerActions(): array;

    /**
     * @return class-string<EvaluatorInterface>
     */
    abstract protected function evaluatorClass(): string;
}
