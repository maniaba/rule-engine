<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine;

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Builders\BuilderInterface;
use Maniaba\RuleEngine\Rules\RuleSet;

final class RuleEngineService
{
    private BuilderInterface $builder;

    public function __construct()
    {
        $this->builder = new ArrayBuilder();
    }

    public function getBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    public function setBuilder(BuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function build(mixed $rules): RuleSet
    {
        return $this->builder->build($rules);
    }
}
