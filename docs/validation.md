# Validation

This page explains how to validate rule configurations to ensure they are correctly structured and will work as expected.

## Why Validate Rule Configurations?

Rule configurations can become complex, especially for large rule sets. Validation helps to:

1. Catch errors early in the development process
2. Ensure that all required fields are present
3. Verify that values are of the correct type
4. Check that referenced actions and conditions exist
5. Prevent runtime errors during rule execution

## Built-in Validation

The Rule Engine performs basic validation when building rules:

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Exceptions\BuilderException;

$builder = new ArrayBuilder();

try {
    $config = [
        'node' => 'condition',
        // Missing 'if' key
        'then' => [
            'node' => 'action',
            'actionName' => 'someAction',
        ],
    ];

    $ruleSet = $builder->build($config);
} catch (BuilderException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
    // Output: Validation error: Invalid node structure: 'if' key is missing.
}
```

## Schema Validation

For more comprehensive validation, you can use a schema validation library like JSON Schema to validate your rule configurations before passing them to the builder:

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Validators\SchemaValidator;

// Create a validator
$validator = new SchemaValidator();

// Define your rule configuration
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

// Validate the configuration
$validationResult = $validator->validate($config);

if (!$validationResult->isValid()) {
    foreach ($validationResult->getErrors() as $error) {
        echo "Validation error: {$error->getPath()} - {$error->getMessage()}\n";
    }
} else {
    // Configuration is valid, proceed with building the rule set
    $builder = new ArrayBuilder();
    $ruleSet = $builder->build($config);
}
```

## Custom Validation Rules

You can also create custom validation rules for your specific requirements:

```php
<?php

class CustomRuleValidator
{
    private array $errors = [];

    public function validate(array $config): bool
    {
        $this->errors = [];

        // Validate node type
        if (!isset($config['node'])) {
            $this->errors[] = "Missing 'node' key";
            return false;
        }

        // Validate based on node type
        switch ($config['node']) {
            case 'condition':
                return $this->validateConditionNode($config);
            case 'action':
                return $this->validateActionNode($config);
            case 'context':
                return $this->validateContextNode($config);
            case 'collection':
                return $this->validateCollectionNode($config);
            case 'not':
                return $this->validateNotNode($config);
            default:
                $this->errors[] = "Unknown node type: {$config['node']}";
                return false;
        }
    }

    private function validateConditionNode(array $config): bool
    {
        if (!isset($config['if'])) {
            $this->errors[] = "Condition node missing 'if' key";
            return false;
        }

        if (!isset($config['then'])) {
            $this->errors[] = "Condition node missing 'then' key";
            return false;
        }

        // Recursively validate the 'if' condition
        if (!$this->validate($config['if'])) {
            return false;
        }

        // Recursively validate the 'then' action
        if (!$this->validate($config['then'])) {
            return false;
        }

        // Optionally validate the 'else' action if present
        if (isset($config['else']) && !$this->validate($config['else'])) {
            return false;
        }

        return true;
    }

    private function validateActionNode(array $config): bool
    {
        if (!isset($config['actionName'])) {
            $this->errors[] = "Action node missing 'actionName' key";
            return false;
        }

        // Additional validation for specific actions could be added here

        return true;
    }

    private function validateContextNode(array $config): bool
    {
        if (!isset($config['contextName'])) {
            $this->errors[] = "Context node missing 'contextName' key";
            return false;
        }

        if (!isset($config['operator'])) {
            $this->errors[] = "Context node missing 'operator' key";
            return false;
        }

        // Validate operator
        $validOperators = [
            'equal', 'greaterThan', 'lessThan', 'greaterThanOrEqual',
            'lessThanOrEqual', 'startsWith', 'endsWith', 'contains',
            'arrayContains', 'arrayContainsAll', 'arrayContainsAny', 'inRange'
        ];

        if (!in_array($config['operator'], $validOperators)) {
            $this->errors[] = "Invalid operator: {$config['operator']}";
            return false;
        }

        // Validate that value is present for most operators
        if (!isset($config['value']) && $config['operator'] !== 'isEmpty' && $config['operator'] !== 'isNotEmpty') {
            $this->errors[] = "Context node missing 'value' key for operator: {$config['operator']}";
            return false;
        }

        return true;
    }

    private function validateCollectionNode(array $config): bool
    {
        if (!isset($config['type'])) {
            $this->errors[] = "Collection node missing 'type' key";
            return false;
        }

        if (!in_array($config['type'], ['and', 'or'])) {
            $this->errors[] = "Invalid collection type: {$config['type']}";
            return false;
        }

        if (!isset($config['nodes']) || !is_array($config['nodes'])) {
            $this->errors[] = "Collection node missing 'nodes' array";
            return false;
        }

        if (empty($config['nodes'])) {
            $this->errors[] = "Collection node has empty 'nodes' array";
            return false;
        }

        // Recursively validate each node in the collection
        foreach ($config['nodes'] as $index => $node) {
            if (!$this->validate($node)) {
                $this->errors[] = "Invalid node at index $index in collection";
                return false;
            }
        }

        return true;
    }

    private function validateNotNode(array $config): bool
    {
        if (!isset($config['condition'])) {
            $this->errors[] = "Not node missing 'condition' key";
            return false;
        }

        // Recursively validate the condition
        if (!$this->validate($config['condition'])) {
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

// Usage
$validator = new CustomRuleValidator();
if (!$validator->validate($config)) {
    foreach ($validator->getErrors() as $error) {
        echo "Validation error: $error\n";
    }
}
```

## Validating Action Names

It's important to validate that all action names referenced in your rule configurations are registered with the action factory:

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;

function validateActionNames(array $config, ArrayBuilder $builder): array
{
    $errors = [];
    $actionFactory = $builder->actions();

    // Helper function to recursively find all action nodes
    $findActionNodes = function(array $node) use (&$findActionNodes): array {
        $actions = [];

        if (isset($node['node']) && $node['node'] === 'action') {
            if (isset($node['actionName'])) {
                $actions[] = $node['actionName'];
            }
        }

        // Recursively check child nodes
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $actions = array_merge($actions, $findActionNodes($value));
            }
        }

        return $actions;
    };

    // Find all action names in the configuration
    $actionNames = $findActionNodes($config);

    // Check if each action name is registered
    foreach ($actionNames as $actionName) {
        if (!$actionFactory->has($actionName)) {
            $errors[] = "Action '$actionName' is not registered";
        }
    }

    return $errors;
}

// Usage
$builder = new ArrayBuilder();
$builder->actions()->registerAction('approveUser', function(ArrayContext $context) { return true; });
$builder->actions()->registerAction('log', function(ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
    return true;
});

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
        'actionName' => 'approveUser', // This is registered
    ],
    'else' => [
        'node' => 'action',
        'actionName' => 'rejectUser', // This is not registered
    ],
];

$errors = validateActionNames($config, $builder);
foreach ($errors as $error) {
    echo "Validation error: $error\n";
    // Output: Validation error: Action 'rejectUser' is not registered
}
```

## Best Practices for Validation

1. **Validate early**: Validate rule configurations as early as possible, ideally before they are used to build rule sets.

2. **Use schema validation**: For complex rule configurations, use a schema validation library to ensure they match the expected structure.

3. **Check for required actions**: Verify that all action names referenced in your rule configurations are registered with the action factory.

4. **Validate context fields**: If possible, validate that the context fields referenced in your rule configurations will be available at runtime.

5. **Test with sample data**: Test your rule configurations with sample data to ensure they behave as expected.

6. **Use descriptive error messages**: Provide clear and descriptive error messages that help identify the exact issue with the configuration.

## Next Steps

Now that you understand how to validate rule configurations, you can:

1. Learn about [troubleshooting](troubleshooting.md) common issues
2. Explore [advanced usage](advanced-usage.md) patterns
3. Check out [examples](examples.md) of rule configurations
