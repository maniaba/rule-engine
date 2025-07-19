<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Builders;

use Maniaba\RuleEngine\Factories\ActionFactory;
use Maniaba\RuleEngine\Factories\ConditionFactory;
use Maniaba\RuleEngine\Rules\RuleSet;

interface BuilderInterface
{
    public function build(mixed $config): RuleSet;

    public function actions(): ActionFactory;

    public function conditions(): ConditionFactory;
}


