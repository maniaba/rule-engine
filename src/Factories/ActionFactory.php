<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Factories;

use Closure;
use InvalidArgumentException;
use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Context\ContextInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Tests\Factories\ActionFactoryTest;
use Throwable;

/**
 * Factory for creating action objects based on configuration.
 *
 * This factory manages a registry of actions that can be created from configuration arrays.
 * It supports creating actions from:
 * - Action class names
 * - Action instances
 * - Callable functions
 *
 * @see ActionFactoryTest
 */
final class ActionFactory
{
    /**
     * @var array<string, ActionInterface|class-string<ActionInterface>|Closure(ContextInterface):mixed>
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
     * @throws InvalidArgumentException if the 'actionName' is missing, unsupported, or the action creation fails
     * @throws ReflectionException
     */
    public function create(array $config): ActionInterface
    {
        $actionName = $config['actionName'] ?? null;
        $arguments  = $config['arguments'] ?? [];

        if (null === $actionName) {
            throw new InvalidArgumentException('Action name is missing.');
        }

        if (! \is_string($actionName)) {
            throw new InvalidArgumentException('Action name must be a string.');
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

        throw new InvalidArgumentException("Unsupported action: {$actionName}");
    }

    /**
     * @param array<string, ActionInterface|class-string<ActionInterface>|Closure(ContextInterface $context):mixed> $actions
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
     * @param ActionInterface|class-string<ActionInterface>|Closure(ContextInterface $context):mixed $action
     * */
    public function registerAction(string $name, ActionInterface|Closure|string $action): void
    {
        if (\is_string($action) && ! is_subclass_of($action, ActionInterface::class)) {
            throw new InvalidArgumentException('Action must implement ' . ActionInterface::class);
        }

        $this->actions[$name] = $action;
    }

    /**
     * Creates a CallableAction from a callable function.
     *
     * @param callable $action     The callable to wrap in a CallableAction
     * @param string   $actionName The name of the action (used for error messages)
     * @param array    $arguments  The arguments to pass to the callable
     *
     * @return CallableAction The created action
     */
    private function makeCallable(callable $action, string $actionName, array $arguments): CallableAction
    {
        $reflection = new ReflectionFunction($action);

        // Remove 'context' parameter as it will be provided during execution
        $parameters = array_filter($reflection->getParameters(), static fn ($parameter): bool => $parameter->getName() !== 'context');

        $arguments = self::testParameters($parameters, $arguments, $actionName);

        return new CallableAction($action, ...$arguments);
    }

    /**
     * Validates and prepares arguments for a callable or class constructor.
     *
     * This method:
     * 1. Filters arguments to only include those that match parameter names
     * 2. Validates argument types against parameter types
     * 3. Applies default values for optional parameters
     * 4. Checks that all required parameters have values
     *
     * @param list<ReflectionParameter> $parameters Reflection parameters to validate against
     * @param array                     $arguments  Arguments provided for the callable or constructor
     * @param string                    $actionName Name of the action (for error messages)
     *
     * @return array Validated and prepared arguments
     *
     * @throws InvalidArgumentException If arguments are missing or of invalid types
     */
    private static function testParameters(array $parameters, array $arguments, string $actionName): array
    {
        $arguments = array_intersect_key($arguments, array_flip(array_map(static fn ($p) => $p->getName(), $parameters)));

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (\array_key_exists($name, $arguments)) {
                $parameterType = $parameter->getType();

                if ($parameterType instanceof ReflectionUnionType) {
                    $valid = false;

                    foreach ($parameterType->getTypes() as $type) {
                        $typeName     = $type instanceof ReflectionUnionType ? 'mixed' : ($type->getName() ?? 'mixed');
                        $validTypes   = [$typeName, 'mixed'];
                        $argumentType = self::gettype($arguments[$name]);

                        if (\in_array($argumentType, $validTypes, true)) {
                            $valid = true;
                            break;
                        }
                    }

                    if (! $valid) {
                        $typeNames = [];

                        foreach ($parameterType->getTypes() as $type) {
                            $typeNames[] = $type instanceof ReflectionUnionType ? 'mixed' : ($type->getName() ?? 'mixed');
                        }

                        throw new InvalidArgumentException("Invalid type for argument: '{$name}'. Expected one of: " . implode(', ', $typeNames) . ', got: ' . ($argumentType ?? \gettype($arguments[$name])));
                    }
                } else {
                    $parameterTypeName = $parameterType instanceof ReflectionNamedType ? $parameterType->getName() : null;
                    $argumentType      = self::gettype($arguments[$name]);

                    // Check if the type is nullable or matches the argument type
                    if (! \in_array($parameterTypeName, [null, 'mixed'], true) && (! ($parameterType !== null && $parameterType->allowsNull() && null === $arguments[$name]) && $parameterTypeName !== $argumentType)) {
                        throw new InvalidArgumentException("Invalid type for argument: '{$name}'. Expected: {$parameterTypeName}, got: " . $argumentType);
                    }
                }
            }

            // check if default value is passed for optional parameters
            if ($parameter->isDefaultValueAvailable() && ! \array_key_exists($name, $arguments)) {
                $arguments[$name] = $parameter->getDefaultValue();
            }

            // check if required parameters are passed
            if ($parameter->isDefaultValueAvailable() === false && ! \array_key_exists($name, $arguments)) {
                throw new InvalidArgumentException("Missing required argument: '{$name}' for action: '{$actionName}'");
            }
        }

        return $arguments;
    }

    /**
     * Gets the normalized type name of a value.
     *
     * This method converts PHP's gettype() output to match the type names used in reflection.
     * For example, 'integer' becomes 'int', 'boolean' becomes 'bool', etc.
     *
     * @param mixed $value The value to get the type of
     *
     * @return string The normalized type name
     */
    private static function gettype(mixed $value): string
    {
        $result = \gettype($value);

        // Map gettype output to corresponding reflection type names
        $typeMap = [
            'integer' => 'int',
            'boolean' => 'bool',
            'double'  => 'float', // gettype() returns 'double' for float
        ];

        if (\array_key_exists($result, $typeMap)) {
            $result = $typeMap[$result];
        }

        return $result;
    }

    /**
     * Creates an action instance from a class name.
     *
     * This method uses reflection to:
     * 1. Check the constructor parameters
     * 2. Validate the provided arguments
     * 3. Instantiate the action class with the correct arguments
     *
     * @param string $action     The fully qualified class name of the action
     * @param string $actionName The name of the action (for error messages)
     * @param array  $arguments  The arguments to pass to the constructor
     *
     * @return ActionInterface The created action instance
     *
     * @throws InvalidArgumentException If the action creation fails
     * @throws ReflectionException      If reflection fails
     */
    private function makeActionClass(string $action, string $actionName, array $arguments): ActionInterface
    {
        // Use reflection to get constructor parameters and validate arguments
        $reflection  = new ReflectionClass($action);
        $constructor = $reflection->getConstructor();

        if (null === $constructor) {
            return new $action();
        }

        $arguments = self::testParameters($constructor->getParameters(), $arguments, $actionName);

        try {
            return new $action(...$arguments);
        } catch (Throwable $e) {
            throw new InvalidArgumentException("Failed to create action: {$actionName}. Error: {$e->getMessage()}", 0, $e);
        }
    }
}
