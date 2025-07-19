# Troubleshooting

This page provides solutions to common issues you might encounter when working with the Rule Engine.

## Common Issues and Solutions

### Rules Not Executing as Expected

**Issue**: Your rules are not executing as expected or producing unexpected results.

**Solutions**:

1. **Check rule evaluation order**: Rules are executed in order of priority. Make sure your rules have the correct priority set.

   ```php
   // Set a higher priority for rules that should execute first
   $rule->setPriority(10);
   ```

2. **Verify context data**: Ensure that your context contains all the required data with the correct types.

   ```php
   // Debug context data
   echo "Context data:\n";
   print_r($context->getField('fieldName'));
   ```

3. **Check condition logic**: Verify that your conditions are correctly structured. Complex nested conditions can be particularly error-prone.

   ```php
   // Simplify complex conditions for debugging
   $simpleCondition = new EqualsCondition('testField', true);
   $result = $simpleCondition->isSatisfied($context);
   echo "Simple condition result: " . ($result ? 'true' : 'false') . "\n";
   ```

4. **Use event listeners for debugging**: Add event listeners to track rule execution and identify where things go wrong.

   ```php
   use Maniaba\RuleEngine\Managers\EventManager;

   $eventManager = EventManager::getInstance();
   $eventManager->addListener('rule.before_evaluate', function(array $data) {
       echo "Evaluating rule...\n";
   });
   $eventManager->addListener('rule.after_evaluate', function(array $data) {
       echo "Rule evaluation result: " . ($data['result'] ? 'true' : 'false') . "\n";
   });
   ```

### Missing Actions

**Issue**: You're getting errors about missing actions when executing rules.

**Solutions**:

1. **Register all required actions**: Make sure all actions referenced in your rule configurations are registered with the action factory.

   ```php
   $builder->actions()->registerAction('actionName', function(ContextInterface $context, array $arguments = []) {
       // Action implementation
       return true;
   });
   ```

2. **Check for typos in action names**: Ensure that the action names in your rule configurations match exactly with the registered action names.

3. **Validate action names before execution**: Use the validation techniques described in the [Validation](validation.md) page to check for missing actions before executing rules.

### Performance Issues

**Issue**: Rule execution is slow, especially with large rule sets.

**Solutions**:

1. **Optimize rule order**: Place rules that are more likely to be satisfied first in AND collections, and rules that are more likely to fail first in OR collections.

2. **Reduce rule complexity**: Break down complex rules into simpler ones. This can make them easier to evaluate and maintain.

3. **Cache rule sets**: If your rules don't change frequently, build them once and cache the result.

   ```php
   // Cache the built rule set
   $cacheKey = 'rule_set_' . md5(serialize($config));
   if ($cache->has($cacheKey)) {
       $ruleSet = $cache->get($cacheKey);
   } else {
       $ruleSet = $builder->build($config);
       $cache->set($cacheKey, $ruleSet);
   }
   ```

4. **Profile rule execution**: Use profiling tools to identify which rules or conditions are taking the most time to evaluate.

   ```php
   $startTime = microtime(true);
   $ruleSet->execute($context);
   $endTime = microtime(true);
   echo "Execution time: " . ($endTime - $startTime) . " seconds\n";
   ```

### Builder Exceptions

**Issue**: You're getting `BuilderException` when building rule sets.

**Solutions**:

1. **Check rule configuration structure**: Ensure that your rule configurations have all required fields and follow the correct structure.

2. **Validate configurations before building**: Use the validation techniques described in the [Validation](validation.md) page to check for issues before building rule sets.

3. **Check for circular references**: Ensure that your rule configurations don't contain circular references, which can cause infinite recursion during building.

4. **Debug with simpler configurations**: Start with a simple rule configuration that works, then gradually add complexity to identify where the issue occurs.

### Context Field Access Issues

**Issue**: Rules can't access fields in the context or are accessing the wrong fields.

**Solutions**:

1. **Check field names**: Ensure that the field names in your rule configurations match exactly with the field names in your context.

2. **Debug context data**: Print the context data to verify that it contains the expected fields with the correct values.

   ```php
   // For ArrayContext
   print_r($context->getField('fieldName'));

   // Check if a field exists
   if ($context->hasField('fieldName')) {
       echo "Field exists\n";
   } else {
       echo "Field does not exist\n";
   }
   ```

3. **Use appropriate context type**: Make sure you're using the appropriate context type (ArrayContext or ObjectContext) for your data structure.

4. **Check for nested fields**: If you're trying to access nested fields, ensure that your context implementation supports this.

## Debugging Techniques

### Logging Rule Execution

Add logging to track rule execution and identify issues:

```php
<?php

use Maniaba\RuleEngine\Managers\EventManager;

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
    // Or write to a log file
    // file_put_contents('rule_engine.log', "[$timestamp] $message\n", FILE_APPEND);
}

$eventManager = EventManager::getInstance();

$eventManager->addListener('ruleset.before_execute', function(array $data) {
    logMessage("Starting rule set execution");
});

$eventManager->addListener('rule.before_evaluate', function(array $data) {
    $rule = $data['rule'];
    logMessage("Evaluating rule (priority: {$rule->getPriority()})");
});

$eventManager->addListener('rule.after_evaluate', function(array $data) {
    $result = $data['result'] ? 'satisfied' : 'not satisfied';
    logMessage("Rule evaluation result: $result");
    if (!$data['result']) {
        $failureMessage = $data['rule']->getFailureMessage();
        if ($failureMessage) {
            logMessage("Failure reason: " . (is_array($failureMessage) ? implode(', ', $failureMessage) : $failureMessage));
        }
    }
});

$eventManager->addListener('rule.before_execute', function(array $data) {
    logMessage("Executing rule actions");
});

$eventManager->addListener('rule.after_execute', function(array $data) {
    if ($errors = $data['rule']->getExecutionErrors()) {
        logMessage("Rule execution errors: " . implode(', ', $errors));
    } else {
        logMessage("Rule executed successfully");
    }
});

$eventManager->addListener('ruleset.after_execute', function(array $data) {
    logMessage("Rule set execution completed");
});
```

### Inspecting Rule Structure

To debug issues with rule structure, you can create a function to print the structure of a rule configuration:

```php
<?php

function printRuleStructure(array $config, int $indent = 0) {
    $indentStr = str_repeat('  ', $indent);

    if (!isset($config['node'])) {
        echo "{$indentStr}Invalid node: missing 'node' key\n";
        return;
    }

    echo "{$indentStr}Node type: {$config['node']}\n";

    switch ($config['node']) {
        case 'condition':
            echo "{$indentStr}If:\n";
            if (isset($config['if'])) {
                printRuleStructure($config['if'], $indent + 1);
            } else {
                echo str_repeat('  ', $indent + 1) . "Missing 'if' key\n";
            }

            echo "{$indentStr}Then:\n";
            if (isset($config['then'])) {
                printRuleStructure($config['then'], $indent + 1);
            } else {
                echo str_repeat('  ', $indent + 1) . "Missing 'then' key\n";
            }

            if (isset($config['else'])) {
                echo "{$indentStr}Else:\n";
                printRuleStructure($config['else'], $indent + 1);
            }
            break;

        case 'context':
            echo "{$indentStr}Context name: " . ($config['contextName'] ?? 'missing') . "\n";
            echo "{$indentStr}Operator: " . ($config['operator'] ?? 'missing') . "\n";
            if (isset($config['value'])) {
                $valueStr = is_array($config['value'])
                    ? '[' . implode(', ', $config['value']) . ']'
                    : (is_bool($config['value'])
                        ? ($config['value'] ? 'true' : 'false')
                        : $config['value']);
                echo "{$indentStr}Value: $valueStr\n";
            }
            break;

        case 'collection':
            echo "{$indentStr}Type: " . ($config['type'] ?? 'missing') . "\n";
            if (isset($config['nodes']) && is_array($config['nodes'])) {
                echo "{$indentStr}Nodes:\n";
                foreach ($config['nodes'] as $index => $node) {
                    echo "{$indentStr}  Node $index:\n";
                    printRuleStructure($node, $indent + 2);
                }
            } else {
                echo "{$indentStr}  Missing or invalid 'nodes' key\n";
            }
            break;

        case 'not':
            echo "{$indentStr}Not:\n";
            if (isset($config['condition'])) {
                printRuleStructure($config['condition'], $indent + 1);
            } else {
                echo str_repeat('  ', $indent + 1) . "Missing 'condition' key\n";
            }
            break;

        case 'action':
            echo "{$indentStr}Action name: " . ($config['actionName'] ?? 'missing') . "\n";
            if (isset($config['arguments']) && is_array($config['arguments'])) {
                $argStr = implode(', ', array_map(function($arg) {
                    return is_array($arg) ? json_encode($arg) : $arg;
                }, $config['arguments']));
                echo "{$indentStr}Arguments: [$argStr]\n";
            }
            break;

        default:
            echo "{$indentStr}Unknown node type: {$config['node']}\n";
    }
}

// Usage
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

echo "Rule structure:\n";
printRuleStructure($config);
```

## Error Messages and Their Meaning

Here are some common error messages you might encounter and what they mean:

### "Invalid node structure: 'node' key is missing"

This error occurs when a configuration array doesn't have a 'node' key, which is required for all nodes in the rule configuration.

**Solution**: Ensure that all nodes in your rule configuration have a 'node' key with a valid value ('condition', 'action', 'context', 'collection', or 'not').

### "Invalid node structure: 'if' key is missing"

This error occurs when a 'condition' node doesn't have an 'if' key, which is required for condition nodes.

**Solution**: Ensure that all condition nodes in your rule configuration have an 'if' key with a valid condition configuration.

### "Invalid node structure: 'then' key is missing"

This error occurs when a 'condition' node doesn't have a 'then' key, which is required for condition nodes.

**Solution**: Ensure that all condition nodes in your rule configuration have a 'then' key with a valid action configuration.

### "Unsupported node type: X"

This error occurs when a node has an unsupported type.

**Solution**: Ensure that all nodes in your rule configuration have a valid type ('condition', 'action', 'context', 'collection', or 'not').

### "Action 'X' is not registered"

This error occurs when a rule references an action that hasn't been registered with the action factory.

**Solution**: Register all actions referenced in your rule configurations with the action factory before building the rule set.

## Getting Help

If you're still having issues after trying the solutions on this page, you can:

1. **Check the source code**: The Rule Engine is open source, so you can check the source code to understand how it works and identify issues.

2. **Create a minimal reproducible example**: Create a simple example that demonstrates the issue you're experiencing. This can help isolate the problem and make it easier to solve.

3. **Ask for help**: Reach out to the community or the maintainers of the Rule Engine for help.

## Next Steps

Now that you know how to troubleshoot common issues with the Rule Engine, you can:

1. Explore [examples](examples.md) of rule configurations
2. Learn about [advanced usage](advanced-usage.md) patterns
3. Check out [validation](validation.md) techniques for rule configurations
