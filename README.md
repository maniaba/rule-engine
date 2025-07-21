# Rule Engine

[![PHPUnit](https://github.com/maniaba/rule-engine/actions/workflows/phpunit.yml/badge.svg)](https://github.com/maniaba/rule-engine/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/maniaba/rule-engine/actions/workflows/phpstan.yml/badge.svg)](https://github.com/maniaba/rule-engine/actions/workflows/phpstan.yml)
[![Psalm](https://github.com/maniaba/rule-engine/actions/workflows/psalm.yml/badge.svg)](https://github.com/maniaba/rule-engine/actions/workflows/psalm.yml)
[![Coverage Status](https://coveralls.io/repos/github/maniaba/rule-engine/badge.svg?branch=develop)](https://coveralls.io/github/maniaba/rule-engine?branch=develop)
[![Docs](https://github.com/maniaba/rule-engine/actions/workflows/docs.yml/badge.svg)](https://github.com/maniaba/rule-engine/actions/workflows/docs.yml)

![PHP](https://img.shields.io/badge/PHP-%5E8.1-blue)
![License](https://img.shields.io/badge/License-MIT-blue)

The Rule Engine is a flexible and extensible framework for defining and evaluating rules and conditions, and executing corresponding actions based on specified conditions.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require maniaba/rule-engine
```

For detailed installation instructions and verification, see the [Installation Documentation](https://maniaba.github.io/rule-engine/installation/).

## Key Features

- Define rules using a structured array configuration
- Combine multiple conditions with logical operators (AND, OR)
- Execute actions based on condition results
- Extend with custom conditions and actions
- Validate rule configurations

## Quick Example

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a builder
$builder = new ArrayBuilder();

// Register an action
$builder->actions()->registerAction('printMessage', function(ArrayContext $context, string $message) {
    echo "Message: {$message}\n";
    return true;
});

// Define a simple rule
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'context',
        'contextName' => 'value',
        'operator' => 'greaterThan',
        'value' => 10,
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'printMessage',
        'arguments' => [
            'message' => 'Value is greater than 10',
        ],
    ],
];

// Build and execute the rule
$ruleSet = $builder->build($config);
$context = new ArrayContext(['value' => 15]);
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();
$evaluator->execute(clone $ruleSet, $context);
```

## Documentation

Comprehensive documentation is available at [https://maniaba.github.io/rule-engine/](https://maniaba.github.io/rule-engine/), including:

- [Detailed Configuration Guide](https://maniaba.github.io/rule-engine/configuration/)
- [Advanced Usage Examples](https://maniaba.github.io/rule-engine/examples/)
- [Custom Conditions and Actions](https://maniaba.github.io/rule-engine/advanced-usage/)
- [Validation and Error Handling](https://maniaba.github.io/rule-engine/validation/)

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the rule engine? Feel free to create an issue on GitHub, we'll try to address it as soon as possible.

## Testing

```bash
composer test
```

## Changelog

All notable changes to this project are documented in the [CHANGELOG.md](CHANGELOG.md) file.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please send an email to [maniaba@outlook.com](mailto:maniaba@outlook.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
