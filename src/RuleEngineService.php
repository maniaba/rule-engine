<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine;

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Builders\BuilderInterface;
use Maniaba\RuleEngine\Rules\RuleSet;

/**
 * Main service class for the Rule Engine.
 *
 * This class serves as the primary entry point for the rule engine system.
 * It manages a builder that constructs rule sets from configuration data.
 */
final class RuleEngineService
{
    /**
     * The builder used to construct rule sets.
     */
    private BuilderInterface $builder;

    /**
     * Initializes a new instance of the RuleEngineService.
     *
     * By default, it uses an ArrayBuilder for constructing rule sets.
     */
    public function __construct()
    {
        $this->builder = new ArrayBuilder();
    }

    /**
     * Gets the current builder instance.
     *
     * @return BuilderInterface The current builder used by this service.
     */
    public function getBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    /**
     * Sets a custom builder for this service.
     *
     * @param BuilderInterface $builder The builder to use for constructing rule sets.
     *
     * @return self Returns the service instance for method chaining.
     */
    public function setBuilder(BuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Builds a rule set from the provided rules configuration.
     *
     * @param mixed $rules The rules configuration data to build from.
     *
     * @return RuleSet The constructed rule set containing the rules defined in the configuration.
     */
    public function build(mixed $rules): RuleSet
    {
        return $this->builder->build($rules);
    }
}
