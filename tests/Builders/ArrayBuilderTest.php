<?php

declare(strict_types=1);

namespace Tests\Builders;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Conditions\GreaterThanOrEqualCondition;
use Maniaba\RuleEngine\Conditions\IfElseCondition;
use Maniaba\RuleEngine\Conditions\NotCondition;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Exceptions\BuilderException;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Actions\DummyArgumentsAction;
use Tests\Support\TestCase;

/**
 * @internal
 *
 * @description Testing the ArrayBuilder class. Tests building a RuleSet based on configuration from the self::configBuilder() array.
 */
#[Group('Others')]
final class ArrayBuilderTest extends TestCase
{
    use BuilderTestDemoConfigTrait;

    public function testBuildWithNonArrayConfigThrowsException(): void
    {
        $builder = new ArrayBuilder();
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Configuration must be an array.');
        $builder->build('not-an-array');
    }

    public function testBuildWithMissingNodeThrowsException(): void
    {
        $builder = new ArrayBuilder();

        // Missing 'node' key
        $config = [
            'if' => [
                'node'        => 'context',
                'contextName' => 'depositCount',
                'operator'    => 'equal',
                'value'       => 10,
            ],
        ];

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage("Invalid node structure: 'node' key is missing.");
        $builder->build($config);
    }

    public function testBuildWithUnsupportedNodeTypeThrowsException(): void
    {
        $builder = new ArrayBuilder();

        $config = [
            'node' => 'unknownNodeType',
        ];

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Unsupported node type: unknownNodeType');
        $builder->build($config);
    }

    public function testBuildWithContextNodeMissingContextName(): void
    {
        $builder = new ArrayBuilder();

        // context node without contextName
        $config = [
            'node'     => 'context',
            'operator' => 'equal',
            'value'    => 10,
        ];

        // In ConditionFactory we expect an error to be thrown if contextName is missing.
        // If ConditionFactory doesn't throw, ArrayBuilder should throw because the config is invalid.
        // In this case, ArrayBuilder will try to create ConditionConfig and most likely get an error because
        // contextName is not defined.

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Invalid node structure: Context name is required.');
        $builder->build($config);
    }

    public function testBuildConditionNodeWithoutIfThrowsException(): void
    {
        $builder = new ArrayBuilder();

        // condition node without 'if'
        $config = [
            'node' => 'condition',
            // 'if' is missing
        ];

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage("Invalid node structure: 'if' key is missing in 'condition' node.");
        // The message should come from the buildCondition method when trying to access $config['if'] which doesn't exist
        // or from the absence of a valid condition.
        // You can adjust the message in the code if you want it to be more specific.
        $builder->build($config);
    }

    public function testBuildValidConfigReturnsRuleSet(): void
    {
        $builder = new ArrayBuilder();

        $config = [
            'node'        => 'context',
            'contextName' => 'depositCount',
            'operator'    => 'equal',
            'value'       => 10,
        ];

        $ruleSet = $builder->build($config);
        $this->assertCount(1, $ruleSet->getRules(), 'There should be 1 rule in the RuleSet');
    }

    public function testBuildRuleSetWithNotCondition(): void
    {
        // Rule configuration
        $config = [
            [
                'node' => 'condition',
                'if'   => [
                    'node'      => 'not',
                    'condition' => [
                        'node'        => 'context',
                        'contextName' => 'depositCount',
                        'operator'    => 'greaterThanOrEqual',
                        'value'       => 5,
                    ],
                ],
                'then' => [
                    'node'       => 'action',
                    'actionName' => 'rejectDeposit',
                ],
            ],
        ];

        // Create RuleSet from configuration
        $builder = new ArrayBuilder();
        // dummy action
        $builder->actions()->registerAction('rejectDeposit', new CallableAction(static fn (ContextInterface $context): bool => true));

        $ruleSet = $builder->build($config);

        // Check that RuleSet is created
        $this->assertCount(1, $ruleSet->getRules());

        // Check the first component of the rule
        $rule = $ruleSet->getRules()[0];
        /**
         * @var IfElseCondition $condition
         */
        $condition = $this->getPrivateProperty($rule, 'condition');

        // Check if the 'if' part is a negation
        $this->assertInstanceOf(IfElseCondition::class, $condition);

        $conditionNot = $this->getPrivateProperty($condition, 'ifCondition');
        $this->assertInstanceOf(NotCondition::class, $conditionNot);

        // Check inside NotCondition if it contains the correct condition
        $innerCondition = $this->getPrivateProperty($conditionNot, 'condition');
        $this->assertInstanceOf(GreaterThanOrEqualCondition::class, $innerCondition);

        $contextName = $this->getPrivateProperty($innerCondition, 'contextName');
        $value       = $this->getPrivateProperty($innerCondition, 'value');

        $this->assertSame('depositCount', $contextName);
        $this->assertSame(5, $value);

        // Check if the 'then' part is an action
        /**
         * @var ActionInterface $action
         */
        $action = $this->getPrivateProperty($condition, 'thenAction');
        $this->assertInstanceOf(ActionInterface::class, $action);
    }

    public function testBuildActionWithArguments(): void
    {
        $builder = new ArrayBuilder();
        $builder->actions()->registerAction('dummy_args', DummyArgumentsAction::class);

        $config = [
            'node'       => 'action',
            'actionName' => 'dummy_args',
            'arguments'  => [
                'arguments' => ['arg1', 'arg2'],
            ],
        ];

        $ruleSet = $builder->build($config);
        $this->assertCount(1, $ruleSet->getRules(), 'There should be 1 rule in the RuleSet');

        $context = $this->createMock(ContextInterface::class);

        $ruleSet->execute($context);
    }

    protected function getBuilder(): RuleSet
    {
        $builder = new ArrayBuilder();

        $dummyAction = new CallableAction(static fn (ContextInterface $context): bool => true);
        $builder->actions()->registerAction('actionName1', $dummyAction);
        $builder->actions()->registerAction('actionName2', $dummyAction);
        $builder->actions()->registerAction('rejectDeposit', $dummyAction);
        $builder->actions()->registerAction('actionName3', $dummyAction);
        $builder->actions()->registerAction('actionName4', $dummyAction);

        return $builder->build($this->configBuilder());
    }
}
