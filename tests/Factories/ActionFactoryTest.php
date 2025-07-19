<?php

declare(strict_types=1);

namespace Tests\Factories;

use InvalidArgumentException;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Context\ContextInterface;
use Maniaba\RuleEngine\Factories\ActionFactory;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Factories\TestAction;
use Tests\Support\Factories\TestActionWithConstructorException;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class ActionFactoryTest extends TestCase
{
    private ActionFactory $actionFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actionFactory = new ActionFactory();
    }

    public function testCreateWithValidActionClass(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestAction::class);

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'testAction',
            'arguments'  => ['field' => 'testField', 'value' => 42, 'valueNonSigned' => 'optional', 'null' => null],
        ]);

        // Assert
        $this->assertInstanceOf(TestAction::class, $action);
    }

    public function testCreateWithInvalidActionName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action name is missing.');

        $this->actionFactory->create([]);
    }

    public function testCreateWithNonExistentAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported action: invalidAction');

        $this->actionFactory->create(['actionName' => 'invalidAction']);
    }

    public function testCreateWithCallable(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableAction', static fn (ContextInterface $context): string => 'test');

        // Act
        $action = $this->actionFactory->create(['actionName' => 'callableAction']);

        // Assert
        $this->assertInstanceOf(CallableAction::class, $action);
    }

    public function testCreateCallableWithMixedArguments(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableWithArgs', static fn (ContextInterface $context, mixed $arg1, mixed $arg2 = null): string => $arg1);

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'callableWithArgs',
            'arguments'  => [
                'arg1' => 'testArg',
            ],
        ]);

        // Assert
        $this->assertInstanceOf(CallableAction::class, $action);
    }

    public function testCreateWithNonStringActionName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action name must be a string.');

        $this->actionFactory->create(['actionName' => 123]);
    }

    public function testRegisterActions(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestAction::class);

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'testAction',
            'arguments'  => ['field' => 'testField', 'value' => 42, 'valueNonSigned' => 'optional', 'null' => null],
        ]);

        // Assert
        $this->assertInstanceOf(TestAction::class, $action);
    }

    public function testCreateCallableWithArguments(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableWithArgs', static fn (ContextInterface $context, string $arg1, int $arg2): string => "{$arg1} - {$arg2}");

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'callableWithArgs',
            'arguments'  => [
                'arg1' => 'testArg',
                'arg2' => 123,
            ],
        ]);

        // Assert
        $this->assertInstanceOf(CallableAction::class, $action);
    }

    public function testCreateCallableWithMissingArguments(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableWithArgs', static fn (ContextInterface $context, string $arg1, int $arg2): string => "{$arg1} - {$arg2}");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required argument: 'arg2' for action: 'callableWithArgs'");

        // Act
        $this->actionFactory->create([
            'actionName' => 'callableWithArgs',
            'arguments'  => [
                'arg1' => 'testArg',
            ],
        ]);
    }

    public function testCreateCallableWithInvalidTypeArguments(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableWithArgs', static fn (ContextInterface $context, string $arg1, int $arg2): string => "{$arg1} - {$arg2}");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type for argument: 'arg2'. Expected: int, got: string");

        // Act
        $this->actionFactory->create([
            'actionName' => 'callableWithArgs',
            'arguments'  => [
                'arg1' => 'testArg',
                'arg2' => 'invalid',
            ],
        ]);
    }

    public function testCreateActionWithoutConstructor(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestActionWithConstructorException::class);

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'testAction',
        ]);

        // Assert
        $this->assertInstanceOf(TestActionWithConstructorException::class, $action);
    }

    // test action withoput constructor

    public function testCreateActionWithInstance(): void
    {
        // Arrange
        $action = new TestAction('testField', null, 42, 'optional');

        $this->actionFactory->registerAction('testAction', $action);

        // Act
        $createdAction = $this->actionFactory->create([
            'actionName' => 'testAction',
        ]);

        // Assert
        $this->assertInstanceOf(TestAction::class, $createdAction);
    }

    // test with instance of action

    public function testCreateCallableWithDefaultValue(): void
    {
        // Arrange
        $this->actionFactory->registerAction('callableWithArgs', static fn (ContextInterface $context, string $arg1, int $arg2 = 42): string => "{$arg1} - {$arg2}");

        // Act
        $action = $this->actionFactory->create([
            'actionName' => 'callableWithArgs',
            'arguments'  => [
                'arg1' => 'testArg',
            ],
        ]);

        // Assert
        $this->assertInstanceOf(CallableAction::class, $action);
    }

    public function testCreateWithMissingArguments(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestAction::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required argument: 'null' for action: 'testAction'");

        // Act
        $this->actionFactory->create([
            'actionName' => 'testAction',
            'arguments'  => ['field' => 'testField'],
        ]);
    }

    public function testRegisterAndUnregisterActions(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestAction::class);

        // Assert Registered
        $action = $this->actionFactory->create([
            'actionName' => 'testAction',
            'arguments'  => ['field' => 'testField', 'value' => 42, 'valueNonSigned' => 'optional', 'null' => null],
        ]);
        $this->assertInstanceOf(TestAction::class, $action);

        // Unregister
        $this->actionFactory->unregisterAction('testAction');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported action: testAction');

        $this->actionFactory->create(['actionName' => 'testAction']);
    }

    public function testResetClearsAllActions(): void
    {
        // Arrange
        $this->actionFactory->registerAction('testAction', TestAction::class);

        // Act
        $this->actionFactory->reset();

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported action: testAction');

        $this->actionFactory->create(['actionName' => 'testAction']);
    }
}
