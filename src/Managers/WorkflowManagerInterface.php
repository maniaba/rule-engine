<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Managers;

use JsonException;
use Maniaba\RuleEngine\Evaluators\Results\EvaluatorErrors;
use Maniaba\RuleEngine\Exceptions\BuilderException;
use Maniaba\RuleEngine\Rules\RuleSet;

interface WorkflowManagerInterface
{
    /**
     * @throws JsonException
     * @throws BuilderException
     */
    public function builder(array|string $config): RuleSet;

    public function getErrors(): EvaluatorErrors;
}
