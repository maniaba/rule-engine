# Events

The Rule Engine provides an event system that allows you to hook into various points in the rule execution lifecycle. This page explains how to use events to extend and customize the Rule Engine's behavior.

## Available Events

The Rule Engine emits events at key points during rule execution:

1. **Before Rule Evaluation**: Triggered before a rule's condition is evaluated
2. **After Rule Evaluation**: Triggered after a rule's condition has been evaluated
3. **Before Rule Execution**: Triggered before a rule's actions are executed
4. **After Rule Execution**: Triggered after a rule's actions have been executed
5. **Before RuleSet Execution**: Triggered before a rule set begins execution
6. **After RuleSet Execution**: Triggered after a rule set completes execution

## Event Listeners

You can register event listeners to respond to these events. Event listeners are functions that receive event data and can perform additional operations.

### Registering Event Listeners

To register an event listener, use the event manager:

```php
<?php

use Maniaba\RuleEngine\Managers\EventManager;
use Maniaba\RuleEngine\Rules\Rule;
use Maniaba\RuleEngine\Rules\RuleSet;

// Get the event manager instance
$eventManager = EventManager::getInstance();

// Register a listener for the "before rule evaluation" event
$eventManager->addListener('rule.before_evaluate', function(array $data) {
    $rule = $data['rule']; // The Rule instance
    $context = $data['context']; // The Context instance

    echo "Before evaluating rule...\n";

    // You can modify the context here if needed
    // For example, add additional data that might be needed for evaluation
});

// Register a listener for the "after rule execution" event
$eventManager->addListener('rule.after_execute', function(array $data) {
    $rule = $data['rule']; // The Rule instance
    $context = $data['context']; // The Context instance
    $result = $data['result']; // The result of the rule evaluation

    echo "Rule execution completed with result: " . ($result ? 'true' : 'false') . "\n";

    // You can perform logging, analytics, or other post-execution tasks here
});
```

### Available Event Names

The Rule Engine uses the following event names:

- `rule.before_evaluate`: Triggered before a rule's condition is evaluated
- `rule.after_evaluate`: Triggered after a rule's condition has been evaluated
- `rule.before_execute`: Triggered before a rule's actions are executed
- `rule.after_execute`: Triggered after a rule's actions have been executed
- `ruleset.before_execute`: Triggered before a rule set begins execution
- `ruleset.after_execute`: Triggered after a rule set completes execution

## Event Data

Each event provides specific data that you can use in your event listeners:

### Rule Events

For rule events (`rule.before_evaluate`, `rule.after_evaluate`, `rule.before_execute`, `rule.after_execute`), the event data includes:

- `rule`: The Rule instance being evaluated or executed
- `context`: The Context instance containing the data for evaluation
- `result`: (Only for "after" events) The result of the evaluation or execution

### RuleSet Events

For rule set events (`ruleset.before_execute`, `ruleset.after_execute`), the event data includes:

- `ruleset`: The RuleSet instance being executed
- `context`: The Context instance containing the data for evaluation
- `results`: (Only for "after" events) An array of results from all rules in the set

## Example: Logging Rule Execution

Here's a complete example of using events to log rule execution:

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;
use Maniaba\RuleEngine\Managers\EventManager;

// Create a simple logging function
function logEvent($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
}

// Get the event manager instance
$eventManager = EventManager::getInstance();

// Register event listeners
$eventManager->addListener('ruleset.before_execute', function(array $data) {
    logEvent("Starting rule set execution");
});

$eventManager->addListener('rule.before_evaluate', function(array $data) {
    $rule = $data['rule'];
    logEvent("Evaluating rule (priority: {$rule->getPriority()})");
});

$eventManager->addListener('rule.after_evaluate', function(array $data) {
    $result = $data['result'] ? 'satisfied' : 'not satisfied';
    logEvent("Rule evaluation result: $result");
});

$eventManager->addListener('rule.before_execute', function(array $data) {
    logEvent("Executing rule actions");
});

$eventManager->addListener('rule.after_execute', function(array $data) {
    if ($errors = $data['rule']->getExecutionErrors()) {
        logEvent("Rule execution errors: " . implode(', ', $errors));
    } else {
        logEvent("Rule executed successfully");
    }
});

$eventManager->addListener('ruleset.after_execute', function(array $data) {
    logEvent("Rule set execution completed");
});

// Create a builder
$builder = new ArrayBuilder();

// Register actions
$builder->actions()->registerAction('approveUser', function(ArrayContext $context) {
    echo "User approved!\n";
    return true;
});

$builder->actions()->registerAction('rejectUser', function(ArrayContext $context) {
    echo "User rejected!\n";
    return true;
});

$builder->actions()->registerAction('log', function(ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
    return true;
});

// Define a rule configuration
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

// Build the rule set
$ruleSet = $builder->build($config);

// Create a context with data
$context = new ArrayContext([
    'age' => 25,
]);

// Create an evaluator
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Execute the rule set
$evaluator->execute(clone $ruleSet, $context);

// Output will include log messages for each event
```

## Custom Event Emission

You can also emit your own custom events from your actions or conditions:

```php
<?php

use Maniaba\RuleEngine\Managers\EventManager;

class CustomAction implements ActionInterface
{
    public function execute(ArrayContext $context, string $param1 = '', int $param2 = 0): bool
    {
        // Emit a custom event before performing the action
        EventManager::getInstance()->emit('custom_action.before_execute', [
            'context' => $context,
            'param1' => $param1,
            'param2' => $param2,
        ]);

        // Perform the action
        // ...

        // Emit a custom event after performing the action
        EventManager::getInstance()->emit('custom_action.after_execute', [
            'context' => $context,
            'param1' => $param1,
            'param2' => $param2,
            'result' => $result,
        ]);

        return $result;
    }
}
```

## Best Practices

When working with events, consider these best practices:

1. **Keep listeners lightweight**: Event listeners should perform minimal work to avoid slowing down rule execution.

2. **Don't modify rule behavior in unexpected ways**: Be careful when modifying context data in event listeners, as it can lead to unexpected rule behavior.

3. **Use events for cross-cutting concerns**: Events are ideal for cross-cutting concerns like logging, monitoring, and analytics.

4. **Clean up listeners**: If you no longer need a listener, remove it to prevent memory leaks:

   ```php
   $eventManager->removeListener('rule.before_evaluate', $listenerFunction);
   ```

5. **Use namespaced event names for custom events**: If you emit custom events, use namespaced names to avoid conflicts:

   ```php
   $eventManager->emit('myapp.rules.custom_event', $data);
   ```

## Next Steps

Now that you understand how to use events in the Rule Engine, you can:

1. Learn about [validation](validation.md) techniques for rule configurations
2. Explore [advanced usage](advanced-usage.md) patterns
3. Check out [examples](examples.md) of rule configurations
