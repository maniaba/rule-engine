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
 * @description Testiranje ArrayBuilder klase. Testira da izgradi RuleSet na osnovu konfiguracije iz niza self::configBuilder().
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

        // Nedostaje 'node' ključ
        $config = [
            'if' => [
                'node' => 'context',
                'contextName' => 'depositCount',
                'operator' => 'equal',
                'value' => 10,
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

        // context node bez contextName
        $config = [
            'node' => 'context',
            'operator' => 'equal',
            'value' => 10,
        ];

        // U ConditionFactory očekujemo da se baca greška ako nema contextName.
        // Ako ConditionFactory ne baca, ArrayBuilder treba baciti jer config nije validan.
        // U ovom slučaju, ArrayBuilder će pokušati kreirati ConditionConfig i najvjerovatnije dobiti error jer
        // contextName nije definisan.

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Invalid node structure: Context name is required.');
        $builder->build($config);
    }

    public function testBuildConditionNodeWithoutIfThrowsException(): void
    {
        $builder = new ArrayBuilder();

        // condition node bez 'if'
        $config = [
            'node' => 'condition',
            // 'if' nedostaje
        ];

        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage("Invalid node structure: 'if' key is missing in 'condition' node.");
        // Poruka bi trebala doći iz buildCondition metode kad se pokuša pristupiti $config['if'] koji ne postoji
        // ili iz nepostojanja validnog condition.
        // Možete prilagoditi poruku u kodu ako želite specifičniju.
        $builder->build($config);
    }

    public function testBuildValidConfigReturnsRuleSet(): void
    {
        $builder = new ArrayBuilder();

        $config = [
            'node' => 'context',
            'contextName' => 'depositCount',
            'operator' => 'equal',
            'value' => 10,
        ];

        $ruleSet = $builder->build($config);
        self::assertInstanceOf(RuleSet::class, $ruleSet, 'build treba vratiti RuleSet za validnu konfiguraciju');
        self::assertCount(1, $ruleSet->getRules(), 'Treba biti 1 rule u RuleSet-u');
    }

    public function testBuildRuleSetWithNotCondition(): void
    {
        // Konfiguracija pravila
        $config = [
            [
                'node' => 'condition',
                'if' => [
                    'node' => 'not',
                    'condition' => [
                        'node' => 'context',
                        'contextName' => 'depositCount',
                        'operator' => 'greaterThanOrEqual',
                        'value' => 5,
                    ],
                ],
                'then' => [
                    'node' => 'action',
                    'actionName' => 'rejectDeposit',
                ],
            ],
        ];

        // Kreiramo RuleSet iz konfiguracije
        $builder = new ArrayBuilder();
        // dummy action
        $builder->actions()->registerAction('rejectDeposit', new CallableAction(static fn(): null => null));

        $ruleSet = $builder->build($config);

        // Proveravamo da je kreiran RuleSet
        self::assertInstanceOf(RuleSet::class, $ruleSet);
        self::assertCount(1, $ruleSet->getRules());

        // Proveravamo prvu komponentu pravila
        $rule = $ruleSet->getRules()[0];
        /**
         * @var IfElseCondition $condition
         */
        $condition = $this->getPrivateProperty($rule, 'condition');

        // Proveravamo da li je 'if' deo negacija
        self::assertInstanceOf(IfElseCondition::class, $condition);

        $conditionNot = $this->getPrivateProperty($condition, 'ifCondition');
        self::assertInstanceOf(NotCondition::class, $conditionNot);

        // Proveravamo unutar NotCondition da li sadrži ispravan uslov
        $innerCondition = $this->getPrivateProperty($conditionNot, 'condition');
        self::assertInstanceOf(GreaterThanOrEqualCondition::class, $innerCondition);

        $contextName = $this->getPrivateProperty($innerCondition, 'contextName');
        $value = $this->getPrivateProperty($innerCondition, 'value');

        self::assertSame('depositCount', $contextName);
        self::assertSame(5, $value);

        // Proveravamo da li je 'then' deo akcija
        /**
         * @var ActionInterface $action
         */
        $action = $this->getPrivateProperty($condition, 'thenAction');
        self::assertInstanceOf(ActionInterface::class, $action);
    }

    public function testBuildActionWithArguments(): void
    {
        $builder = new ArrayBuilder();
        $builder->actions()->registerAction('dummy_args', DummyArgumentsAction::class);

        $config = [
            'node' => 'action',
            'actionName' => 'dummy_args',
            'arguments' => [
                'arguments' => ['arg1', 'arg2'],
            ],
        ];

        $ruleSet = $builder->build($config);
        self::assertInstanceOf(RuleSet::class, $ruleSet, 'build treba vratiti RuleSet za validnu konfiguraciju');
        self::assertCount(1, $ruleSet->getRules(), 'Treba biti 1 rule u RuleSet-u');

        $context = $this->createMock(ContextInterface::class);

        $ruleSet->execute($context);
    }

    protected function getBuilder(): RuleSet
    {
        $builder = new ArrayBuilder();

        $dummyAction = new CallableAction(static fn(): null => null);
        $builder->actions()->registerAction('actionName1', $dummyAction);
        $builder->actions()->registerAction('actionName2', $dummyAction);
        $builder->actions()->registerAction('rejectDeposit', $dummyAction);
        $builder->actions()->registerAction('actionName3', $dummyAction);
        $builder->actions()->registerAction('actionName4', $dummyAction);

        return $builder->build($this->configBuilder());
    }
}
