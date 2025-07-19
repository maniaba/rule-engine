<?php

declare(strict_types=1);

namespace Tests\Builders;

use ReflectionException;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Conditions\CollectionCondition;
use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Conditions\EqualsCondition;
use Maniaba\RuleEngine\Conditions\GreaterThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\IfElseCondition;
use Maniaba\RuleEngine\Conditions\LessThanOrEqualCondition;
use Maniaba\RuleEngine\Enums\CollectionConditionType;
use Maniaba\RuleEngine\Rules\RuleSet;

trait BuilderTestDemoConfigTrait
{
    protected static function configBuilder(): mixed
    {
        return [
            'node' => 'condition',
            'if'   => [
                'node'  => 'collection',
                'type'  => 'and',
                'nodes' => [
                    [
                        'node'        => 'context',
                        'contextName' => 'depositCount',
                        'operator'    => 'greaterThanOrEqual',
                        'value'       => 2,
                    ],
                    [
                        'node'        => 'context',
                        'contextName' => 'accountAge',
                        'operator'    => 'greaterThanOrEqual',
                        'value'       => 1,
                    ],
                    [
                        'node'  => 'collection',
                        'type'  => 'or',
                        'nodes' => [
                            [
                                'node'        => 'context',
                                'contextName' => 'hasVipStatus',
                                'operator'    => 'equal',
                                'value'       => true,
                            ],
                            [
                                'node'        => 'context',
                                'contextName' => 'withdrawalCount',
                                'operator'    => 'lessThanOrEqual',
                                'value'       => 5,
                            ],
                        ],
                    ],
                ],
            ],
            'then' => [
                'node' => 'condition',
                'if'   => [
                    'node'  => 'collection',
                    'type'  => 'or',
                    'nodes' => [
                        [
                            'node'        => 'context',
                            'contextName' => 'loanApproved',
                            'operator'    => 'equal',
                            'value'       => true,
                        ],
                        [
                            'node'  => 'collection',
                            'type'  => 'and',
                            'nodes' => [
                                [
                                    'node'        => 'context',
                                    'contextName' => 'monthlyIncome',
                                    'operator'    => 'greaterThanOrEqual',
                                    'value'       => 3000,
                                ],
                                [
                                    'node'        => 'context',
                                    'contextName' => 'creditScore',
                                    'operator'    => 'greaterThanOrEqual',
                                    'value'       => 700,
                                ],
                            ],
                        ],
                    ],
                ],
                'then' => [
                    'node'       => 'action',
                    'actionName' => 'actionName1',
                ],
                'else' => [
                    'node'       => 'action',
                    'actionName' => 'actionName2',
                ],
            ],
            'else' => [
                'node' => 'condition',
                'if'   => [
                    'node'  => 'collection',
                    'type'  => 'and',
                    'nodes' => [
                        [
                            'node'        => 'context',
                            'contextName' => 'accountBlocked',
                            'operator'    => 'equal',
                            'value'       => true,
                        ],
                        [
                            'node'        => 'context',
                            'contextName' => 'fraudReported',
                            'operator'    => 'equal',
                            'value'       => true,
                        ],
                    ],
                ],
                'then' => [
                    'node'       => 'action',
                    'actionName' => 'actionName3',
                ],
                'else' => [
                    'node'       => 'action',
                    'actionName' => 'actionName4',
                ],
            ],
        ];
    }

    public function testBuildRuleSet(): void
    {
        $ruleSet = $this->getBuilder();

        $this->assertCount(1, $ruleSet->getRules());

        $rule = $ruleSet->getRules()[0];

        /**
         * @var IfElseCondition $condition
         */
        $condition = $this->getPrivateProperty($rule, 'condition');

        /**
         * @var IfElseCondition $conditionThenAction
         */
        [$collection, $conditionThenAction, $ifElseConditionAction] = $this->checkIfElseCondition($condition);
        $this->conditionThenAction($conditionThenAction);
        $this->ifElseConditionActionElseAction($ifElseConditionAction);

        /**
         * @var array{GreaterThanOrEqualCondition, GreaterThanOrEqualCondition, CollectionCondition} $conditions
         */
        $conditions = $this->checkCollectionCondition($collection, CollectionConditionType::AND, 3);

        $this->checkInstanceCondition(GreaterThanOrEqualCondition::class, $conditions[0], 'depositCount', 2);
        $this->checkInstanceCondition(GreaterThanOrEqualCondition::class, $conditions[1], 'accountAge', 1);

        $conditions = $this->checkCollectionCondition($conditions[2], CollectionConditionType::OR, 2);
        $this->checkInstanceCondition(EqualsCondition::class, $conditions[0], 'hasVipStatus', true);
        $this->checkInstanceCondition(LessThanOrEqualCondition::class, $conditions[1], 'withdrawalCount', 5);
    }

    abstract protected function getBuilder(): RuleSet;

    /**
     * @return array{array, mixed, mixed}
     *
     * @throws ReflectionException
     */
    private function checkIfElseCondition(IfElseCondition $condition): array
    {
        $ifCondition = $this->getPrivateProperty($condition, 'ifCondition');
        $thenAction  = $this->getPrivateProperty($condition, 'thenAction');
        $elseAction  = $this->getPrivateProperty($condition, 'elseAction');

        return [$ifCondition, $thenAction, $elseAction];
    }

    private function conditionThenAction(IfElseCondition $conditionThenAction): void
    {
        [$collection, $thenAction, $elseAction] = $this->checkIfElseCondition($conditionThenAction);

        $this->assertInstanceOf(CallableAction::class, $thenAction, 'Then action is not instance of CallableAction');
        $this->assertInstanceOf(CallableAction::class, $elseAction, 'Else action is not instance of CallableAction');
        /**
         * @var array{EqualsCondition, CollectionCondition} $conditions
         */
        $conditions = $this->checkCollectionCondition($collection, CollectionConditionType::OR, 2);
        $this->checkInstanceCondition(EqualsCondition::class, $conditions[0], 'loanApproved', true);

        $conditions2 = $this->checkCollectionCondition($conditions[1], CollectionConditionType::AND, 2);
        $this->checkInstanceCondition(GreaterThanOrEqualCondition::class, $conditions2[0], 'monthlyIncome', 3000);
        $this->checkInstanceCondition(GreaterThanOrEqualCondition::class, $conditions2[1], 'creditScore', 700);
    }

    /**
     * @return list<ConditionInterface>
     */
    private function checkCollectionCondition(CollectionCondition $condition, CollectionConditionType $expectedType, int $expectedCount): array
    {
        $this->assertSame($expectedType, $condition->type, 'Condition type is not equal to AND like in config');

        $conditions = $this->getPrivateProperty($condition, 'conditions');

        $this->assertCount($expectedCount, $conditions, 'Condition does not have ' . $expectedCount . ' conditions');

        return $conditions;
    }

    private function checkInstanceCondition(string $expectedClass, ConditionInterface $condition, string $expectedContextName, mixed $expectedValue): void
    {
        $this->assertInstanceOf($expectedClass, $condition, 'Condition is not instance of ' . $expectedClass);
        $contextName = $this->getPrivateProperty($condition, 'contextName');
        $value       = $this->getPrivateProperty($condition, 'value');

        $this->assertSame($expectedContextName, $contextName, 'Condition contextName is not equal to ' . $expectedContextName . ' like in config');
        $this->assertSame($expectedValue, $value, 'Condition value is not equal to ' . $expectedValue . ' like in config');
    }

    /**
     * @throws ReflectionException
     */
    private function ifElseConditionActionElseAction(IfElseCondition $conditionThenAction): void
    {
        /**
         * @var CollectionCondition $collection
         */
        [$collection, $thenAction, $elseAction] = $this->checkIfElseCondition($conditionThenAction);

        $conditions = $this->checkCollectionCondition($collection, CollectionConditionType::AND, 2);
        $this->checkInstanceCondition(EqualsCondition::class, $conditions[0], 'accountBlocked', true);
        $this->checkInstanceCondition(EqualsCondition::class, $conditions[1], 'fraudReported', true);

        $this->assertInstanceOf(CallableAction::class, $thenAction, 'Then action is not instance of CallableAction');
        $this->assertInstanceOf(CallableAction::class, $elseAction, 'Else action is not instance of CallableAction');
    }
}

