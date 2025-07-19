<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Builders;

use InvalidArgumentException;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Conditions\AlwaysTrueCondition;
use Maniaba\RuleEngine\Conditions\CollectionCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Conditions\IfElseCondition;
use Maniaba\RuleEngine\Conditions\NotCondition;
use Maniaba\RuleEngine\Exceptions\BuilderException;
use Maniaba\RuleEngine\Factories\ActionFactory;
use Maniaba\RuleEngine\Factories\ConditionFactory;
use Maniaba\RuleEngine\Rules\Rule;
use Maniaba\RuleEngine\Rules\RuleSet;
use ReflectionException;
use Tests\Builders\ArrayBuilderTest;

/**
 * @see ArrayBuilderTest
 */
class ArrayBuilder implements BuilderInterface
{
    private ActionFactory $actions;
    private ConditionFactory $conditions;

    public function build(mixed $config): RuleSet
    {
        if (! \is_array($config)) {
            throw new BuilderException('Configuration must be an array.');
        }

        // check if we have multiple rules or just one or kex is not numeric
        if (\array_key_exists('node', $config) || ! is_numeric(key($config))) {
            // creating array of rules
            $config = [$config];
        }

        $ruleSet = new RuleSet();

        foreach ($config as $ruleConfig) {
            $rule = $this->buildRule($ruleConfig);
            $ruleSet->addRule($rule);
        }

        return $ruleSet;
    }

    public function conditions(): ConditionFactory
    {
        if (! isset($this->conditions)) {
            $this->conditions = new ConditionFactory();
        }

        return $this->conditions;
    }

    public function actions(): ActionFactory
    {
        if (! isset($this->actions)) {
            $this->actions = new ActionFactory();
        }

        return $this->actions;
    }

    /**
     * Kreira Rule na osnovu konfiguracije.
     *
     * @param array $config konfiguracija za pravilo
     */
    private function buildRule(array $config): Rule
    {
        if (! \array_key_exists('node', $config)) {
            throw new BuilderException("Invalid node structure: 'node' key is missing.");
        }

        switch ($config['node']) {
            case 'collection':
            case 'condition':
                $condition = $this->buildCondition($config);

                $thenActions = $this->buildActions($config['then'] ?? []);
                $elseActions = $this->buildActions($config['else'] ?? []);

                return new Rule($condition, $thenActions, $elseActions);

            case 'action':
                $actions = $this->buildActions($config);

                return new Rule(new AlwaysTrueCondition(), $actions, []);

            case 'context':
                try {
                    $context = $this->conditions()->create($config);
                } catch (InvalidArgumentException $e) {
                    throw new BuilderException("Invalid node structure: {$e->getMessage()}");
                }

                return new Rule($context, [], []);

            default:
                throw new BuilderException("Unsupported node type: {$config['node']}");
        }
    }

    /**
     * Kreira ConditionInterface na osnovu konfiguracije.
     *
     * @param array $config konfiguracija uslova
     */
    private function buildCondition(array $config): ConditionInterface
    {
        if (! isset($config['node'])) {
            throw new BuilderException("Invalid node structure: 'node' key is missing.");
        }

        switch ($config['node']) {
            case 'not':
                if (\array_key_exists('condition', $config)) {
                    $config['condition'] = $this->buildCondition($config['condition']);
                }
                $node = NotCondition::class;
                break;

            case 'collection':
                // Build nodes
                if (\array_key_exists('nodes', $config) && \is_array($config['nodes'])) {
                    $config['nodes'] = array_map(fn (array $node): ConditionInterface => $this->buildCondition($node), $config['nodes']);
                }
                $node = CollectionCondition::class;
                break;

            case 'condition':
                if (\array_key_exists('if', $config)) {
                    $config['if'] = $this->buildCondition($config['if']);
                }
                $config = $this->buildActionOrCondition($config, 'then');
                $config = $this->buildActionOrCondition($config, 'else');

                $node = IfElseCondition::class;
                break;

            case 'context':
                try {
                    return $this->conditions()->create($config);
                } catch (InvalidArgumentException $e) {
                    throw new BuilderException("Invalid node structure: {$e->getMessage()}");
                }

            default:
                throw new BuilderException("Unsupported node type: {$config['node']}");
        }

        try {
            return $node::factory($config);
        } catch (InvalidArgumentException $e) {
            throw new BuilderException("Invalid node structure: {$e->getMessage()}");
        }
    }

    private function buildActionOrCondition(array &$config, string $key): array
    {
        if (\array_key_exists($key, $config)) {
            // Ako je '$key' čvor akcija, inače je uslov
            if (isset($config[$key]['node']) && 'action' === $config[$key]['node']) {
                $config[$key] = $this->buildActions($config[$key]);

                if (\count($config[$key]) > 1) {
                    throw new BuilderException("Multiple actions are not supported in one 'action' node.");
                }
                $config[$key] = end($config[$key]);
            } else {
                $config[$key] = $this->buildCondition($config[$key]);
            }
        }

        return $config;
    }

    /**
     * Kreira niz ActionInterface ili ConditionInterface na osnovu konfiguracije.
     *
     * @return list<ActionInterface>
     */
    private function buildActions(mixed $actionsConfig): array
    {
        if (! \is_array($actionsConfig)) {
            throw new BuilderException('Actions configuration must be an array.');
        }

        // Ako je jedan čvor, pretvaramo ga u niz za uniformnost
        if (isset($actionsConfig['node'])) {
            $actionsConfig = [$actionsConfig];
        }

        return array_map(function ($config): ActionInterface|ConditionInterface {
            switch ($config['node']) {
                case 'action':
                    if (! \array_key_exists('actionName', $config)) {
                        throw new BuilderException("Invalid node structure: 'actionName' key is missing in 'action' node.");
                    }

                    try {
                        return $this->actions->create($config);
                    } catch (InvalidArgumentException $e) {
                        throw new BuilderException("Invalid node structure: {$e->getMessage()}");
                    } catch (ReflectionException $e) {
                        throw new BuilderException("Generic error: {$e->getMessage()}");
                    }

                default:
                    return $this->buildCondition($config);
            }
        }, $actionsConfig);
    }
}
