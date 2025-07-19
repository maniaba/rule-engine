# Rule Engine Documentation

## Overview

The Rule Engine is a flexible and extensible framework for defining and evaluating rules and conditions, and executing corresponding actions based on specified conditions. This documentation provides detailed information on how to configure, use, and extend the Rule Engine.

---

## Table of Contents

1. [Getting Started](#getting-started)

2. [Key Concepts](#key-concepts)
    - [Conditions](#conditions)
    - [Actions](#actions)

3. [Rule Structure](#rule-structure)
    - [Node Types](#node-types)
    - [Example Rule Configuration](#example-rule-configuration)

4. [Configuration and Usage](#configuration-and-usage)

5. [Advanced Topics](#advanced-topics)
    - [Custom Conditions](#custom-conditions)
    - [Custom Actions](#custom-actions)

6. [Best Practices](#best-practices)

7. [Frequently Asked Questions (FAQ)](#frequently-asked-questions)

---

## Getting Started

To get started with the Rule Engine:

1. Install the library into your project.

2. Define your rules using a structured array configuration.

3. Use the `ArrayBuilder` class to build a `RuleSet`.

4. Execute the rules with a provided context.

For more detailed installation instructions, see the [Installation](installation.md) page.

---

## Key Concepts

### Conditions

Conditions define logical checks to evaluate data within a given context. Examples of conditions include:

- Checking if a number is greater than or equal to a specific value.
- Verifying if a string starts with a given prefix.
- Checking if an array contains a specific value.

The Rule Engine provides a variety of built-in conditions:

- `EqualsCondition`: Checks if a value equals another value
- `GreaterThanCondition`: Checks if a value is greater than another value
- `LessThanCondition`: Checks if a value is less than another value
- `GreaterThanOrEqualCondition`: Checks if a value is greater than or equal to another value
- `LessThanOrEqualCondition`: Checks if a value is less than or equal to another value
- `StartsWithCondition`: Checks if a string starts with a specific prefix
- `EndsWithCondition`: Checks if a string ends with a specific suffix
- `StringContainCondition`: Checks if a string contains a specific substring
- `ArrayContainsCondition`: Checks if an array contains a specific value
- `ArrayContainsAllCondition`: Checks if an array contains all specified values
- `ArrayContainsAnyCondition`: Checks if an array contains any of the specified values
- `NumericInRangeCondition`: Checks if a number is within a specified range
- `NotCondition`: Negates the result of another condition
- `CollectionCondition`: Combines multiple conditions with logical operators (AND, OR)

### Actions

Actions define the operations to be executed when conditions are met or not met. Examples include:

- Triggering a custom function or callback.
- Updating a database field.
- Sending notifications.

The Rule Engine provides a `CallableAction` that allows you to use any callable function as an action.

---

## Rule Structure

Rules consist of **conditions** and **actions**. The condition specifies the logic to be evaluated, while actions define what should happen based on the result of the evaluation.

### Node Types

The Rule Engine supports the following node types:

- **`condition`**: Represents a conditional logic node.
- **`collection`**: A group of conditions evaluated with logical operators (`and`, `or`).
- **`context`**: A condition that retrieves a value from the context and evaluates it using an operator.
- **`not`**: Negates the result of a condition.
- **`action`**: Represents an action to execute.

### Example Rule Configuration

Below is a comprehensive example of a rule configuration:

```php
$config = [
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
                        'node'        => 'not',
                        'condition'   => [
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
                                    'node'        => 'not',
                                    'condition'   => [
                                        'node'        => 'context',
                                        'contextName' => 'withdrawalCount',
                                        'operator'    => 'lessThanOrEqual',
                                        'value'       => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'then' => [
        'node'       => 'action',
        'actionName' => 'actionName1',
        'arguments'  => ['arg1', 'arg2'],
    ],
    'else' => [
        'node'       => 'action',
        'actionName' => 'actionName2',
    ],
];
```

---

## Configuration and Usage

### Building a RuleSet

1. **Define your rule configuration**: Use the structure described above to define your rules.
2. **Build the RuleSet**:
   ```php
   use Maniaba\RuleEngine\Builders\ArrayBuilder;

   $builder = new ArrayBuilder();
   $ruleSet = $builder->build($config);
   ```

3. **Execute the RuleSet**:
   ```php
   use Maniaba\RuleEngine\Context\ArrayContext;

   $context = new ArrayContext([
       'depositCount' => 3,
       'accountAge' => 2,
       'hasVipStatus' => true,
       'withdrawalCount' => 0,
   ]);

   $ruleSet->execute($context);
   ```

### Customizing Actions

Actions can be customized using the `ActionFactory` class. Arguments can be passed from the configuration.

For more detailed usage instructions, see the [Basic Usage](basic-usage.md) and [Advanced Usage](advanced-usage.md) pages.

---

## Advanced Topics

### Custom Conditions

You can create custom conditions by implementing the `ConditionInterface`. For example:

```php
final class CustomCondition implements ConditionInterface
{
    public function __construct(private string $contextName, private mixed $expectedValue) {}

    public static function factory(array $data): ConditionInterface
    {
        return new self($data['contextName'], $data['expectedValue']);
    }

    public function isSatisfied(ContextInterface $context): bool
    {
        return $context->getField($this->contextName) === $this->expectedValue;
    }

    public function getFailureMessage(): array|string|null
    {
        return "Custom condition failed";
    }
}
```

### Custom Actions

Custom actions can be implemented by extending the `ActionInterface`. For example:

```php
final class NotifyAction implements ActionInterface
{
    public function execute(ContextInterface $context, array $arguments = []): bool
    {
        echo "Notification sent with arguments: " . implode(', ', $arguments);
        return true;
    }
}
```

---

## Best Practices

1. **Keep configurations modular**: Break down complex rules into smaller, manageable configurations.
2. **Use meaningful action names**: Ensure action names clearly describe their purpose.
3. **Validate configurations**: Use validation utilities to check the structure of your rule configurations.
4. **Handle errors gracefully**: Check for execution errors after running rules.
5. **Use appropriate context types**: Choose between `ArrayContext` and `ObjectContext` based on your data structure.

---

## Frequently Asked Questions (FAQ)

### Q: Can I nest conditions?
**A**: Yes, conditions can be nested using the `collection` or `not` nodes.

### Q: How do I debug failing conditions?
**A**: Use the `getFailureMessage` method provided by conditions to understand why a condition failed.

### Q: Can I reuse conditions or actions across multiple rules?
**A**: Yes, you can reuse condition and action definitions as long as the logic aligns.

### Q: How do I prioritize rules?
**A**: Use the `setPriority` method on rules to set their priority. Rules with higher priority values will be executed first.

---

## Conclusion

The Rule Engine is a powerful tool for building dynamic, extensible rule-based systems. By following this documentation, you can configure and execute rules tailored to your application's requirements. For further customization, extend the provided interfaces to create new conditions and actions.
