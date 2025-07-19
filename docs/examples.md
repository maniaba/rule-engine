# Examples

This page provides practical examples of using the Rule Engine in various scenarios. These examples demonstrate how to implement common use cases and can serve as templates for your own rule configurations.

## User Access Control

This example demonstrates how to implement access control rules based on user roles and permissions.

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a builder
$builder = new ArrayBuilder();

// Register actions
$builder->actions()->registerAction('grantAccess', function() {
    echo "Access granted\n";
    return true;
});

$builder->actions()->registerAction('denyAccess', function() {
    echo "Access denied\n";
    return true;
});

// Define rule configuration
$config = [
    'node' => 'condition',
    'if' => [
        'node' => 'collection',
        'type' => 'or',
        'nodes' => [
            // Admin role has full access
            [
                'node' => 'context',
                'contextName' => 'role',
                'operator' => 'equal',
                'value' => 'admin',
            ],
            // Editor role with approved status
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
                        'contextName' => 'isApproved',
                        'operator' => 'equal',
                        'value' => true,
                    ],
                ],
            ],
            // User with specific permission
            [
                'node' => 'context',
                'contextName' => 'permissions',
                'operator' => 'arrayContains',
                'value' => 'view_reports',
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

// Build the rule set
$ruleSet = $builder->build($config);

// Test with different contexts
$adminContext = new ArrayContext([
    'role' => 'admin',
    'isApproved' => false,
    'permissions' => [],
]);

$editorContext = new ArrayContext([
    'role' => 'editor',
    'isApproved' => true,
    'permissions' => ['edit_content'],
]);

$userContext = new ArrayContext([
    'role' => 'user',
    'isApproved' => false,
    'permissions' => ['view_reports'],
]);

$guestContext = new ArrayContext([
    'role' => 'guest',
    'isApproved' => false,
    'permissions' => [],
]);

// Execute the rule set with different contexts
echo "Admin: ";
$ruleSet->execute($adminContext); // Output: Admin: Access granted

echo "Editor: ";
$ruleSet->execute($editorContext); // Output: Editor: Access granted

echo "User: ";
$ruleSet->execute($userContext); // Output: User: Access granted

echo "Guest: ";
$ruleSet->execute($guestContext); // Output: Guest: Access denied
```

## E-commerce Discount Rules

This example shows how to implement discount rules for an e-commerce application.

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a builder
$builder = new ArrayBuilder();

// Register actions
$builder->actions()->registerAction('applyDiscount', function(ContextInterface $context, array $arguments = []) {
    $percentage = $arguments[0] ?? 0;
    $totalAmount = $context->getField('totalAmount');
    $discountAmount = $totalAmount * ($percentage / 100);

    echo "Applied {$percentage}% discount: \${$discountAmount}\n";
    echo "Final price: \$" . ($totalAmount - $discountAmount) . "\n";

    return true;
});

$builder->actions()->registerAction('noDiscount', function() {
    echo "No discount applied\n";
    return true;
});

// Define discount rules
$rules = [
    // Rule 1: VIP customers get 15% discount
    [
        'node' => 'condition',
        'if' => [
            'node' => 'context',
            'contextName' => 'customerType',
            'operator' => 'equal',
            'value' => 'vip',
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'applyDiscount',
            'arguments' => [15],
        ],
    ],

    // Rule 2: Orders over $100 get 10% discount
    [
        'node' => 'condition',
        'if' => [
            'node' => 'context',
            'contextName' => 'totalAmount',
            'operator' => 'greaterThanOrEqual',
            'value' => 100,
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'applyDiscount',
            'arguments' => [10],
        ],
    ],

    // Rule 3: First-time customers get 5% discount
    [
        'node' => 'condition',
        'if' => [
            'node' => 'context',
            'contextName' => 'isFirstPurchase',
            'operator' => 'equal',
            'value' => true,
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'applyDiscount',
            'arguments' => [5],
        ],
    ],

    // Rule 4: Default rule - no discount
    [
        'node' => 'condition',
        'if' => [
            'node' => 'collection',
            'type' => 'or',
            'nodes' => [
                [
                    'node' => 'context',
                    'contextName' => 'totalAmount',
                    'operator' => 'lessThan',
                    'value' => 50,
                ],
                [
                    'node' => 'context',
                    'contextName' => 'isDiscountExcluded',
                    'operator' => 'equal',
                    'value' => true,
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'noDiscount',
        ],
    ],
];

// Set rule priorities (higher number = higher priority)
$ruleSet = $builder->build($rules);
$ruleSet->getRules()[0]->setPriority(30); // VIP rule has highest priority
$ruleSet->getRules()[1]->setPriority(20); // Order amount rule has medium priority
$ruleSet->getRules()[2]->setPriority(10); // First purchase rule has lower priority
$ruleSet->getRules()[3]->setPriority(0);  // Default rule has lowest priority

// Test with different contexts
$vipContext = new ArrayContext([
    'customerType' => 'vip',
    'totalAmount' => 120,
    'isFirstPurchase' => false,
    'isDiscountExcluded' => false,
]);

$largeOrderContext = new ArrayContext([
    'customerType' => 'regular',
    'totalAmount' => 150,
    'isFirstPurchase' => false,
    'isDiscountExcluded' => false,
]);

$firstPurchaseContext = new ArrayContext([
    'customerType' => 'regular',
    'totalAmount' => 75,
    'isFirstPurchase' => true,
    'isDiscountExcluded' => false,
]);

$excludedContext = new ArrayContext([
    'customerType' => 'regular',
    'totalAmount' => 200,
    'isFirstPurchase' => false,
    'isDiscountExcluded' => true,
]);

// Execute the rule set with different contexts
echo "VIP Customer:\n";
$ruleSet->execute($vipContext); // Output: Applied 15% discount

echo "\nLarge Order:\n";
$ruleSet->execute($largeOrderContext); // Output: Applied 10% discount

echo "\nFirst Purchase:\n";
$ruleSet->execute($firstPurchaseContext); // Output: Applied 5% discount

echo "\nExcluded Item:\n";
$ruleSet->execute($excludedContext); // Output: No discount applied
```

## Form Validation

This example demonstrates how to use the Rule Engine for form validation.

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a builder
$builder = new ArrayBuilder();

// Register actions
$builder->actions()->registerAction('addError', function(ContextInterface $context, array $arguments = []) {
    $field = $arguments[0] ?? '';
    $message = $arguments[1] ?? 'Invalid value';

    echo "Validation error: {$field} - {$message}\n";
    return true;
});

// Define validation rules
$config = [
    // Username validation
    [
        'node' => 'condition',
        'if' => [
            'node' => 'not',
            'condition' => [
                'node' => 'collection',
                'type' => 'and',
                'nodes' => [
                    [
                        'node' => 'context',
                        'contextName' => 'username',
                        'operator' => 'stringLength',
                        'min' => 3,
                        'max' => 20,
                    ],
                    [
                        'node' => 'context',
                        'contextName' => 'username',
                        'operator' => 'matches',
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                    ],
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'addError',
            'arguments' => ['username', 'Username must be 3-20 characters and contain only letters, numbers, and underscores'],
        ],
    ],

    // Email validation
    [
        'node' => 'condition',
        'if' => [
            'node' => 'not',
            'condition' => [
                'node' => 'context',
                'contextName' => 'email',
                'operator' => 'matches',
                'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'addError',
            'arguments' => ['email', 'Please enter a valid email address'],
        ],
    ],

    // Password validation
    [
        'node' => 'condition',
        'if' => [
            'node' => 'not',
            'condition' => [
                'node' => 'collection',
                'type' => 'and',
                'nodes' => [
                    [
                        'node' => 'context',
                        'contextName' => 'password',
                        'operator' => 'stringLength',
                        'min' => 8,
                    ],
                    [
                        'node' => 'context',
                        'contextName' => 'password',
                        'operator' => 'matches',
                        'pattern' => '/[A-Z]/',
                    ],
                    [
                        'node' => 'context',
                        'contextName' => 'password',
                        'operator' => 'matches',
                        'pattern' => '/[0-9]/',
                    ],
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'addError',
            'arguments' => ['password', 'Password must be at least 8 characters and include at least one uppercase letter and one number'],
        ],
    ],
];

// Build the rule set
$ruleSet = $builder->build($config);

// Test with invalid form data
$invalidFormData = new ArrayContext([
    'username' => 'u$er',
    'email' => 'invalid-email',
    'password' => 'weak',
]);

// Execute validation
echo "Validating form data:\n";
$ruleSet->execute($invalidFormData);
// Output:
// Validation error: username - Username must be 3-20 characters and contain only letters, numbers, and underscores
// Validation error: email - Please enter a valid email address
// Validation error: password - Password must be at least 8 characters and include at least one uppercase letter and one number

// Test with valid form data
$validFormData = new ArrayContext([
    'username' => 'john_doe123',
    'email' => 'john.doe@example.com',
    'password' => 'SecurePass123',
]);

// Execute validation
echo "\nValidating valid form data:\n";
$ruleSet->execute($validFormData);
// No output (no validation errors)
```

## Workflow State Transitions

This example shows how to use the Rule Engine to manage workflow state transitions.

```php
<?php

use Maniaba\RuleEngine\Builders\ArrayBuilder;
use Maniaba\RuleEngine\Context\ArrayContext;

// Create a builder
$builder = new ArrayBuilder();

// Register actions
$builder->actions()->registerAction('transitionTo', function(ContextInterface $context, array $arguments = []) {
    $newState = $arguments[0] ?? '';
    $currentState = $context->getField('currentState');

    echo "Transitioning from '{$currentState}' to '{$newState}'\n";
    return true;
});

$builder->actions()->registerAction('rejectTransition', function(ContextInterface $context, array $arguments = []) {
    $reason = $arguments[0] ?? 'Invalid transition';
    $currentState = $context->getField('currentState');
    $requestedTransition = $context->getField('requestedTransition');

    echo "Rejected transition from '{$currentState}' to '{$requestedTransition}': {$reason}\n";
    return true;
});

// Define workflow rules
$config = [
    // Draft -> Review transition
    [
        'node' => 'condition',
        'if' => [
            'node' => 'collection',
            'type' => 'and',
            'nodes' => [
                [
                    'node' => 'context',
                    'contextName' => 'currentState',
                    'operator' => 'equal',
                    'value' => 'draft',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'requestedTransition',
                    'operator' => 'equal',
                    'value' => 'review',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'isComplete',
                    'operator' => 'equal',
                    'value' => true,
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'transitionTo',
            'arguments' => ['review'],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => ['Document must be complete before review'],
        ],
    ],

    // Review -> Approved transition
    [
        'node' => 'condition',
        'if' => [
            'node' => 'collection',
            'type' => 'and',
            'nodes' => [
                [
                    'node' => 'context',
                    'contextName' => 'currentState',
                    'operator' => 'equal',
                    'value' => 'review',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'requestedTransition',
                    'operator' => 'equal',
                    'value' => 'approved',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'userRole',
                    'operator' => 'equal',
                    'value' => 'manager',
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'transitionTo',
            'arguments' => ['approved'],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => ['Only managers can approve documents'],
        ],
    ],

    // Review -> Rejected transition
    [
        'node' => 'condition',
        'if' => [
            'node' => 'collection',
            'type' => 'and',
            'nodes' => [
                [
                    'node' => 'context',
                    'contextName' => 'currentState',
                    'operator' => 'equal',
                    'value' => 'review',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'requestedTransition',
                    'operator' => 'equal',
                    'value' => 'rejected',
                ],
                [
                    'node' => 'context',
                    'contextName' => 'userRole',
                    'operator' => 'arrayContainsAny',
                    'value' => ['manager', 'reviewer'],
                ],
            ],
        ],
        'then' => [
            'node' => 'action',
            'actionName' => 'transitionTo',
            'arguments' => ['rejected'],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => ['Only managers or reviewers can reject documents'],
        ],
    ],
];

// Build the rule set
$ruleSet = $builder->build($config);

// Test with different contexts
$draftToReviewValid = new ArrayContext([
    'currentState' => 'draft',
    'requestedTransition' => 'review',
    'isComplete' => true,
    'userRole' => 'author',
]);

$draftToReviewInvalid = new ArrayContext([
    'currentState' => 'draft',
    'requestedTransition' => 'review',
    'isComplete' => false,
    'userRole' => 'author',
]);

$reviewToApprovedValid = new ArrayContext([
    'currentState' => 'review',
    'requestedTransition' => 'approved',
    'isComplete' => true,
    'userRole' => 'manager',
]);

$reviewToApprovedInvalid = new ArrayContext([
    'currentState' => 'review',
    'requestedTransition' => 'approved',
    'isComplete' => true,
    'userRole' => 'reviewer',
]);

// Execute the rule set with different contexts
echo "Valid draft to review transition:\n";
$ruleSet->execute($draftToReviewValid);

echo "\nInvalid draft to review transition:\n";
$ruleSet->execute($draftToReviewInvalid);

echo "\nValid review to approved transition:\n";
$ruleSet->execute($reviewToApprovedValid);

echo "\nInvalid review to approved transition:\n";
$ruleSet->execute($reviewToApprovedInvalid);
```

## Next Steps

These examples demonstrate some common use cases for the Rule Engine. You can adapt and extend these examples to fit your specific requirements. For more advanced usage, see the [Advanced Usage](advanced-usage.md) guide.
