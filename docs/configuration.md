# Configuration

This page provides an overview of how to configure the Rule Engine for your specific needs.

## Overview

The Rule Engine is designed to be highly configurable, allowing you to define complex rule sets using a structured array or JSON format. The configuration defines the conditions to evaluate and the actions to execute based on those evaluations.

## Configuration Structure

A rule configuration typically consists of:

1. **Node Type**: Specifies the type of node (condition, collection, context, not, action)
2. **Condition**: The logic to evaluate
3. **Actions**: What to execute when conditions are met or not met

### Basic Configuration Example

Here's a simple example of a rule configuration:

```php
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'context',
        'contextName' => 'userAge',
        'operator' => 'greaterThanOrEqual',
        'value' => 18,
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'allowAccess',
    ],
    'else' => [
        'node' => 'action',
        'actionName' => 'denyAccess',
    ],
];
```

This configuration creates a rule that:
- Checks if the user's age is greater than or equal to 18
- If true, executes the 'allowAccess' action
- If false, executes the 'denyAccess' action

## Node Types

The Rule Engine supports several node types, each with its own configuration options:

### Condition Node

A condition node represents a conditional logic structure with 'if', 'then', and optional 'else' branches.

```php
[
    'node' => 'condition',
    'if' => [...], // Condition to evaluate
    'then' => [...], // Action to execute if condition is true
    'else' => [...], // Optional: Action to execute if condition is false
]
```

### Collection Node

A collection node groups multiple conditions together with a logical operator ('and' or 'or').

```php
[
    'node' => 'collection',
    'type' => 'and', // or 'or'
    'nodes' => [
        [...], // First condition
        [...], // Second condition
        // More conditions...
    ],
]
```

### Context Node

A context node retrieves a value from the context and evaluates it using an operator.

```php
[
    'node' => 'context',
    'contextName' => 'fieldName', // Name of the field in the context
    'operator' => 'equal', // Operator to use for comparison
    'value' => 'expectedValue', // Value to compare against
]
```

### Not Node

A not node negates the result of a condition.

```php
[
    'node' => 'not',
    'condition' => [...], // Condition to negate
]
```

### Action Node

An action node represents an action to execute.

```php
[
    'node' => 'action',
    'actionName' => 'actionName', // Name of the action to execute
    'arguments' => [...], // Optional: Arguments to pass to the action
]
```

## Operators

The Rule Engine supports various operators for comparing values:

- `equal`: Checks if values are equal
- `greaterThan`: Checks if a value is greater than another
- `lessThan`: Checks if a value is less than another
- `greaterThanOrEqual`: Checks if a value is greater than or equal to another
- `lessThanOrEqual`: Checks if a value is less than or equal to another
- `startsWith`: Checks if a string starts with a specific prefix
- `endsWith`: Checks if a string ends with a specific suffix
- `contains`: Checks if a string contains a specific substring
- `arrayContains`: Checks if an array contains a specific value
- `arrayContainsAll`: Checks if an array contains all specified values
- `arrayContainsAny`: Checks if an array contains any of the specified values
- `inRange`: Checks if a number is within a specified range

## Configuration Methods

The Rule Engine provides multiple ways to configure rules:

### Array Configuration

Using PHP arrays is the most common way to configure rules:

```php
use Maniaba\RuleEngine\Builders\ArrayBuilder;

$config = [...]; // Your rule configuration
$builder = new ArrayBuilder();
$ruleSet = $builder->build($config);
```

### JSON Configuration

You can also use JSON for configuration, which is useful for storing rules in a database or configuration file:

```php
use Maniaba\RuleEngine\Builders\JsonBuilder;

$jsonConfig = '{"node":"condition","if":{...},"then":{...}}';
$builder = new JsonBuilder();
$ruleSet = $builder->build($jsonConfig);
```

## Registering Custom Actions

You can register custom actions with the ActionFactory:

```php
$builder = new ArrayBuilder();
$builder->actions()->registerAction('customAction', function(ArrayContext $context, string $param1 = '', int $param2 = 0) {
    // Your custom action logic here
    return true;
});
```

## Next Steps

Now that you understand how to configure the Rule Engine, you can:

1. Learn about [basic usage](basic-usage.md) of the Rule Engine
2. Explore [advanced usage](advanced-usage.md) techniques
3. Check out [examples](examples.md) of rule configurations
