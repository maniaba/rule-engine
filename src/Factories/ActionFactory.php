<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Factories;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Context\ContextInterface;
use Tests\Maniaba\RuleEngine\Factories\ActionFactoryTest;

/**
 * @see ActionFactoryTest
 */
final class ActionFactory
{
    /**
     * @var array<string, ActionInterface|class-string<ActionInterface>|\Closure(ContextInterface):mixed>
     */
    private array $actions = [];

    /**
     * Unregisters an action by removing it from the actions list.
     *
     * @param string $name the name of the action to be unregistered
     */
    public function unregisterAction(string $name): void
    {
        unset($this->actions[$name]);
    }

    /**
     * Resets the actions list by clearing all registered actions.
     */
    public function reset(): void
    {
        $this->actions = [];
    }

    /**
     * Creates an action instance based on the provided configuration.
     *
     * @param array $config The configuration array containing 'actionName' and optional 'arguments'.
     *                      - 'actionName': The name of the action to be created.
     *                      - 'arguments': Optional arguments to pass to the action constructor or callable.
     *
     * @return ActionInterface the created action instance
     *
     * @throws \InvalidArgumentException if the 'actionName' is missing, unsupported, or the action creation fails
     * @throws \ReflectionException
     */
    public function create(array $config): ActionInterface
    {
        $actionName = $config['actionName'] ?? null;
        $arguments = $config['arguments'] ?? [];

        if (null === $actionName) {
            throw new \InvalidArgumentException('Action name is missing.');
        }

        if (! \is_string($actionName)) {
            throw new \InvalidArgumentException('Action name must be a string.');
        }

        $action = $this->actions[$actionName] ?? null;

        if (null !== $action) {
            if (\is_callable($action)) {
                return $this->makeCallable($action, $actionName, $arguments);
            }

            if (\is_string($action) && class_exists($action) && is_subclass_of($action, ActionInterface::class)) {
                return $this->makeActionClass($action, $actionName, $arguments);
            }

            if ($action instanceof ActionInterface) {
                return $action;
            }
        }

        throw new \InvalidArgumentException("Unsupported action: {$actionName}");
    }

    /**
     * @param array<string, ActionInterface|class-string<ActionInterface>|\Closure(ContextInterface $context):mixed> $actions
     */
    public function registerActions(array $actions): void
    {
        foreach ($actions as $name => $action) {
            $this->registerAction($name, $action);
        }
    }

    /**
     * Registers a new action by adding it to the actions list.
     *
     * @param ActionInterface|class-string<ActionInterface>|\Closure(ContextInterface $context):mixed $action
     * */
    public function registerAction(string $name, ActionInterface|\Closure|string $action): void
    {
        if (\is_string($action) && ! is_subclass_of($action, ActionInterface::class)) {
            throw new \InvalidArgumentException('Action must implement '.ActionInterface::class);
        }

        $this->actions[$name] = $action;
    }

    private function makeCallable(callable $action, string $actionName, array $arguments): CallableAction
    {
        $reflection = new \ReflectionFunction($action);

        // remove 'context' name from  getParameters
        $parameters = array_filter($reflection->getParameters(), static fn($parameter): bool => $parameter->getName() !== 'context');

        $arguments = $this->testParameters($parameters, $arguments, $actionName);

        return new CallableAction($action, ...$arguments);
    }

    private function testParameters(array $parameters, array $arguments, string $actionName): array
    {
        $arguments = array_intersect_key($arguments, array_flip(array_map(static fn($p) => $p->getName(), $parameters)));

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (\array_key_exists($name, $arguments)) {
                $parameterType = $parameter->getType();

                if ($parameterType instanceof \ReflectionUnionType) {
                    $valid = false;

                    foreach ($parameterType->getTypes() as $type) {
                        $validTypes = [...array_map(static fn($type) => $type->getName(), [$type]), 'mixed'];
                        $argumentType = self::gettype($arguments[$name]);

                        if (\in_array($argumentType, $validTypes, true)) {
                            $valid = true;
                            break;
                        }
                    }

                    if (! $valid) {
                        throw new \InvalidArgumentException("Invalid type for argument: '{$name}'. Expected one of: ".implode(', ', array_map(static fn($type) => $type->getName(), $parameterType->getTypes())).', got: '.($argumentType ?? \gettype($arguments[$name])));
                    }
                } else {
                    $parameterTypeName = $parameterType?->getName();
                    $argumentType = self::gettype($arguments[$name]);

                    // Provjerite da li je tip nullable ili odgovara tipu argumenta
                    if (! \in_array($parameterTypeName, [null, 'mixed'], true) && (! ($parameterType?->allowsNull() && null === $arguments[$name]) && $parameterTypeName !== $argumentType)) {
                        throw new \InvalidArgumentException("Invalid type for argument: '{$name}'. Expected: {$parameterTypeName}, got: ".$argumentType);
                    }
                }
            }

            // check if default value is passed for optional parameters
            if ($parameter->isDefaultValueAvailable() && ! \array_key_exists($name, $arguments)) {
                $arguments[$name] = $parameter->getDefaultValue();
            }

            // check if required parameters are passed
            if ($parameter->isDefaultValueAvailable() === false && ! \array_key_exists($name, $arguments)) {
                throw new \InvalidArgumentException("Missing required argument: '{$name}' for action: '{$actionName}'");
            }
        }

        return $arguments;
    }

    private static function gettype(mixed $value): string
    {
        $result = \gettype($value);

        // Mapirajte gettype izlaz u odgovarajuće nazive refleksije
        $typeMap = [
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float', // gettype() vraća 'double' za float
        ];

        if (\array_key_exists($result, $typeMap)) {
            $result = $typeMap[$result];
        }

        return $result;
    }

    private function makeActionClass(string $action, string $actionName, array $arguments): ActionInterface
    {
        // reflection get constructor parameters and check if all required parameters are passed and correct type
        $reflection = new \ReflectionClass($action);
        $constructor = $reflection->getConstructor();

        if (null === $constructor) {
            return new $action();
        }

        $arguments = $this->testParameters($constructor->getParameters(), $arguments, $actionName);

        try {
            return new $action(...$arguments);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Failed to create action: {$actionName}. Error: {$e->getMessage()}", 0, $e);
        }
    }
}
