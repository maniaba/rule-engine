# Installation

This guide will help you install and set up the Rule Engine in your project.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation Steps

### 1. Install via Composer

The recommended way to install the Rule Engine is through [Composer](https://getcomposer.org/).

```bash
composer require maniaba/rule-engine
```

### 2. Verify Installation

After installation, you can verify that the Rule Engine is correctly installed by creating a simple test script:

```php
<?php

require 'vendor/autoload.php';

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a simple rule configuration
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'context',
        'contextName' => 'testValue',
        'operator' => 'equal',
        'value' => true,
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'testAction',
        'arguments' => [
            'message' => 'Rule Engine is working correctly!',
        ],
    ],
];

// Create a builder
$builder = new ArrayBuilder();

// Register a test action
$builder->actions()->registerAction('testAction', function (ArrayContext $context, string $message) {
    $testValueFromContext = $context->getField('testValue') ? 'true' : 'false';
    echo "Action executed with message: {$message}\n";
    echo "Test value from context: {$testValueFromContext}\n";
    return true;
});

// Build the rule set
$ruleSet = $builder->build($config);

// Create a context
$context = new ArrayContext(['testValue' => true]);

// Create an evaluator
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Execute the rule set
$evaluator->execute(clone $ruleSet, $context);
```

If you see the messages "Action executed with message: Rule Engine is working correctly!" and "Test value from context: true" when running this script, the Rule Engine is installed and functioning properly.

## Manual Installation

If you prefer not to use Composer, you can manually download the Rule Engine from the [GitHub repository](https://github.com/maniaba/rule-engine) and include the files in your project.

However, this approach is not recommended as it makes it more difficult to manage dependencies and updates.

## Next Steps

Now that you have installed the Rule Engine, you can:

1. Learn about the [basic usage](basic-usage.md) of the Rule Engine
2. Explore the [configuration options](configuration.md)
3. Check out [examples](examples.md) of rule configurations

If you encounter any issues during installation, please refer to the [troubleshooting](troubleshooting.md) section.
