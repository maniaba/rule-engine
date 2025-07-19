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
