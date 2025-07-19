# Advanced Usage

This guide covers advanced usage patterns and techniques for the Rule Engine, helping you leverage its full potential in complex scenarios.

## Custom Conditions

While the Rule Engine provides many built-in conditions, you may need to create custom conditions for specific business logic.

### Creating a Custom Condition

To create a custom condition, implement the `ConditionInterface`:

```php
<?php

namespace YourNamespace\Conditions;

use Maniaba\RuleEngine\Conditions\ConditionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

class CustomBusinessCondition implements ConditionInterface
{
    private string $contextField;
    private array $validValues;
    private ?string $failureMessage = null;

    public function __construct(string $contextField, array $validValues)
    {
        $this->contextField = $contextField;
        $this->validValues = $validValues;
    }

    public static function factory(array $data): ConditionInterface
    {
        return new self(
            $data['contextField'],
            $data['validValues']
        );
    }

    public function isSatisfied(ContextInterface $context): bool
    {
        if (!$context->hasField($this->contextField)) {
            $this->failureMessage = "Field '{$this->contextField}' not found in context";
            return false;
        }

        $value = $context->getField($this->contextField);

        $result = in_array($value, $this->validValues, true);

        if (!$result) {
            $this->failureMessage = "Value '{$value}' is not in the list of valid values";
        }

        return $result;
    }

    public function getFailureMessage(): array|string|null
    {
        return $this->failureMessage;
    }
}
```

### Registering a Custom Condition

Register your custom condition with the condition factory:

```php
use Maniaba\RuleEngine\Builders\ArrayBuilder;
use YourNamespace\Conditions\CustomBusinessCondition;

$builder = new ArrayBuilder();
$builder->conditions()->register('customBusiness', CustomBusinessCondition::class);

// Now you can use it in your configuration
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'customBusiness',
        'contextField' => 'status',
        'validValues' => ['active', 'pending'],
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'processUser',
    ],
];
```

## Custom Actions

Similarly, you can create custom actions for specific operations.

### Creating a Custom Action

Implement the `ActionInterface` to create a custom action:

```php
<?php

namespace YourNamespace\Actions;

use Maniaba\RuleEngine\Actions\ActionInterface;
use Maniaba\RuleEngine\Context\ContextInterface;

class LogAction implements ActionInterface
{
    private string $logMessage;
    private string $logLevel;

    public function __construct(string $logMessage, string $logLevel = 'info')
    {
        $this->logMessage = $logMessage;
        $this->logLevel = $logLevel;
    }

    public function execute(ContextInterface $context, array $arguments = []): bool
    {
        // Replace with your actual logging implementation
        $message = $this->formatMessage($this->logMessage, $context, $arguments);

        // Example logging
        echo "[{$this->logLevel}] {$message}\n";

        return true;
    }

    private function formatMessage(string $template, ContextInterface $context, array $arguments): string
    {
        // Replace placeholders with context values
        $message = preg_replace_callback('/\{(\w+)\}/', function($matches) use ($context) {
            $field = $matches[1];
            return $context->hasField($field) ? (string)$context->getField($field) : $matches[0];
        }, $template);

        // Add arguments if any
        if (!empty($arguments)) {
            $message .= ' ' . json_encode($arguments);
        }

        return $message;
    }
}
```

### Registering a Custom Action

Register your custom action with the action factory:

```php
use Maniaba\RuleEngine\Builders\ArrayBuilder;
use YourNamespace\Actions\LogAction;

$builder = new ArrayBuilder();
$builder->actions()->registerAction('log', function(ContextInterface $context, array $arguments = []) {
    $action = new LogAction($arguments[0] ?? 'No message', $arguments[1] ?? 'info');
    return $action->execute($context, array_slice($arguments, 2));
});

// Now you can use it in your configuration
$config = [
    'node' => 'action',
    'actionName' => 'log',
    'arguments' => ['User {username} processed', 'info'],
];
```

## Complex Rule Structures

The Rule Engine allows you to create complex rule structures by nesting conditions and combining them with logical operators.

### Nested Conditions Example

```php
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'collection',
        'type' => 'and',
        'nodes' => [
            // User must be active
            [
                'node' => 'context',
                'contextName' => 'status',
                'operator' => 'equal',
                'value' => 'active',
            ],
            // AND either have admin role OR have both editor role and approved status
            [
                'node' => 'collection',
                'type' => 'or',
                'nodes' => [
                    [
                        'node' => 'context',
                        'contextName' => 'role',
                        'operator' => 'equal',
                        'value' => 'admin',
                    ],
                    [
                        'node' => 'collection',
                        'type' => 'and',
                        'nodes' => [
                            [
                                'node' => 'context',
                                'contextName' => 'role',
                                'operator' => 'equal',
                                'value' => 'editor',
                            ],
                            [
                                'node' => 'context',
                                'contextName' => 'approved',
                                'operator' => 'equal',
                                'value' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'then' => [
        'node' => 'action',
        'actionName' => 'grantAccess',
    ],
    'else' => [
        'node' => 'action',
        'actionName' => 'denyAccess',
    ],
];
```

## Rule Prioritization

When working with multiple rules, you can set priorities to control the order of execution:

```php
use Maniaba\RuleEngine\Rules\Rule;
use Maniaba\RuleEngine\Rules\RuleSet;

// Create rules
$rule1 = new Rule($condition1, $actions1);
$rule2 = new Rule($condition2, $actions2);
$rule3 = new Rule($condition3, $actions3);

// Set priorities (higher number = higher priority)
$rule1->setPriority(10);
$rule2->setPriority(20); // This will execute first
$rule3->setPriority(5);  // This will execute last

// Add to rule set
$ruleSet = new RuleSet();
$ruleSet->addRule($rule1);
$ruleSet->addRule($rule2);
$ruleSet->addRule($rule3);

// Rules will execute in order of priority: rule2, rule1, rule3
$ruleSet->execute($context);
```

## Dynamic Rule Generation

In some cases, you may need to generate rules dynamically based on runtime conditions:

```php
function generateRules(array $userRoles): array
{
    $rules = [];

    foreach ($userRoles as $role => $permissions) {
        $rules[] = [
            'node' => 'condition',
            'if' => [
                'node' => 'context',
                'contextName' => 'role',
                'operator' => 'equal',
                'value' => $role,
            ],
            'then' => [
                'node' => 'action',
                'actionName' => 'assignPermissions',
                'arguments' => [$permissions],
            ],
        ];
    }

    return $rules;
}

// Usage
$userRoles = [
    'admin' => ['read', 'write', 'delete'],
    'editor' => ['read', 'write'],
    'viewer' => ['read'],
];

$rules = generateRules($userRoles);
$builder = new ArrayBuilder();
$ruleSet = $builder->build($rules);
```

## Performance Optimization

For large rule sets, consider these optimization techniques:

1. **Use early returns**: Place the most likely conditions first in AND collections and the least likely conditions first in OR collections.

2. **Cache rule sets**: If your rules don't change frequently, build them once and cache the result.

3. **Batch similar rules**: Group rules that operate on the same context fields to improve cache locality.

4. **Limit nesting depth**: Deeply nested conditions can be harder to evaluate and maintain.

## Next Steps

Now that you've mastered advanced usage of the Rule Engine, you can:

1. Explore [examples](examples.md) of complex rule configurations
2. Learn about [validation](validation.md) techniques for rule configurations
3. Check out [troubleshooting](troubleshooting.md) tips for common issues
