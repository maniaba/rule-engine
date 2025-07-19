# Basic Usage

This guide covers the fundamental usage patterns of the Rule Engine, helping you get started with implementing rules in your application.

## Creating a Simple Rule

Let's start with a basic example of creating and executing a rule:

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// 1. Create a builder
$builder = new ArrayBuilder();

// 2. Register actions
$builder->actions()->registerAction('approveUser', function() {
    echo "User approved!\n";
    return true;
});

$builder->actions()->registerAction('rejectUser', function() {
    echo "User rejected!\n";
    return true;
});

// 3. Define a rule configuration
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'context',
        'contextName' => 'age',
        'operator' => 'greaterThanOrEqual',
        'value' => 18,
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'approveUser',
    ],
    'else' => [
        'node' => 'action',
        'actionName' => 'rejectUser',
    ],
];

// 4. Build the rule set
$ruleSet = $builder->build($config);

// 5. Create a context with data
$context = new ArrayContext([
    'age' => 25,
]);

// 6. Execute the rule set
$ruleSet->execute($context);
// Output: User approved!
```

## Step-by-Step Explanation

### 1. Create a Builder

The first step is to create a builder that will help construct your rules:

```php
$builder = new ArrayBuilder();
```

The `ArrayBuilder` class allows you to build rules from array configurations.

### 2. Register Actions

Before using actions in your rules, you need to register them with the action factory:

```php
$builder->actions()->registerAction('actionName', function(ContextInterface $context, array $arguments = []) {
    // Action logic here
    return true; // Return true for success, false for failure
});
```

Actions can be any callable function that accepts a context and optional arguments.

### 3. Define Rule Configuration

Rules are defined using a structured array configuration:

```php
$config = [
    'node' => 'condition',
    'if' => [
        // Condition configuration
    ],
    'then' => [
        // Action to execute if condition is true
    ],
    'else' => [
        // Action to execute if condition is false (optional)
    ],
];
```

### 4. Build the Rule Set

Use the builder to create a rule set from your configuration:

```php
$ruleSet = $builder->build($config);
```

### 5. Create a Context

The context provides the data that will be evaluated by your rules:

```php
// Array-based context
$context = new ArrayContext([
    'key1' => 'value1',
    'key2' => 'value2',
]);

// Or object-based context
$context = new ObjectContext($someObject);
```

### 6. Execute the Rule Set

Finally, execute the rule set with your context:

```php
$ruleSet->execute($context);
```

## Working with Multiple Conditions

Often, you'll need to evaluate multiple conditions together. The Rule Engine supports this through collection nodes:

```php
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'collection',
        'type' => 'and',
        'nodes' => [
            [
                'node' => 'context',
                'contextName' => 'age',
                'operator' => 'greaterThanOrEqual',
                'value' => 18,
            ],
            [
                'node' => 'context',
                'contextName' => 'hasVerifiedEmail',
                'operator' => 'equal',
                'value' => true,
            ],
        ],
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'approveUser',
    ],
    'else' => [
        'node' => 'action',
        'actionName' => 'rejectUser',
    ],
];
```

This rule will approve users who are both 18 or older AND have a verified email.

## Using Negation

You can negate conditions using the `not` node:

```php
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'not',
        'condition' => [
            'node' => 'context',
            'contextName' => 'isBlacklisted',
            'operator' => 'equal',
            'value' => true,
        ],
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

This rule will allow access if the user is NOT blacklisted.

## Handling Errors

When executing rules, it's important to check for errors:

```php
$ruleSet->execute($context);

// Check for execution errors
foreach ($ruleSet->getRules() as $rule) {
    if ($errors = $rule->getExecutionErrors()) {
        echo "Rule execution errors: " . implode(', ', $errors) . "\n";
    }
}
```

## Next Steps

Now that you understand the basics of using the Rule Engine, you can:

1. Learn about [advanced usage](advanced-usage.md) techniques
2. Explore [configuration options](configuration.md) in more detail
3. Check out [examples](examples.md) of rule configurations
