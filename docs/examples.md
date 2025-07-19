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
$builder->actions()->registerAction('grantAccess', function(ArrayContext $context) {
    echo "Access granted\n";
    return true;
});

$builder->actions()->registerAction('denyAccess', function(ArrayContext $context) {
    echo "Access denied\n";
    return true;
});

$builder->actions()->registerAction('log', function(ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
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

// Evaluate the rule set with the contexts
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Build the rule set
$ruleSet = $builder->build($config);
$ruleSet1 = $builder->build($config);
$ruleSet2 = $builder->build($config);
$ruleSet3 = $builder->build($config);

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
$evaluator->execute(clone $ruleSet, $adminContext); // Output: Admin: Access granted

echo "Editor: ";
$evaluator->execute(clone $ruleSet1, $editorContext); // Output: Editor: Access granted

echo "User: ";
$evaluator->execute(clone $ruleSet2, $userContext); // Output: User: Access granted

echo "Guest: ";
$evaluator->execute(clone $ruleSet3, $guestContext); // Output: Guest: Access denied
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
$builder->actions()->registerAction('applyDiscount', function (ArrayContext $context, int $percentage, string $message = '') {
    $totalAmount = $context->getField('totalAmount');
    $discountAmount = $totalAmount * ($percentage / 100);

    echo "Applied {$percentage}% discount: \${$discountAmount}\n";
    $finalPrice = $totalAmount - $discountAmount;
    echo "Final price: \$" . ($finalPrice) . "\n";

    if ($message !== '') {
        echo "Message: {$message}\n";
    }

    $context->setField('totalAmount', $finalPrice);

    return true;
});

$builder->actions()->registerAction('noDiscount', function() {
    echo "No discount applied\n";
    return true;
});

$builder->actions()->registerAction('log', function (ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
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
            'arguments' => [
                'percentage' => 15,
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'Customer is not VIP, no discount applied.',
            ],
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
            'arguments' => [
                'percentage' => 10,
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'Order amount is less than $100, no discount applied.',
            ],
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
            'arguments' => [
                'percentage' => 5,
                'message' => 'First-time customer discount applied.',
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'No discount for returning customers.',
            ],
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
            'actionName' => 'log',
            'arguments' => [
                'message' => 'No discount applicable due to low order amount or exclusion.',
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'noDiscount',
        ],
    ],
];

// Evaluate the rule set with the contexts
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Build the rule set
$ruleSet = $builder->build($rules);
$ruleSet1 = $builder->build($rules);
$ruleSet2 = $builder->build($rules);
$ruleSet3 = $builder->build($rules);

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
    'totalAmount' => 40,
    'isFirstPurchase' => false,
    'isDiscountExcluded' => true,
]);

// Execute the rule set with different contexts
echo "VIP Customer:\n";
$evaluator->execute(clone $ruleSet, $vipContext); // Output: Applied 15% discount: $18, Final price: $102, Applied 10% discount: $10.2, Final price: $91.8, Log: No discount for returning customers., No discount applied

echo "\nLarge Order:\n";
$evaluator->execute(clone $ruleSet1, $largeOrderContext); // Output: Log: Customer is not VIP, no discount applied., Applied 10% discount: $15, Final price: $135, Log: No discount for returning customers., No discount applied

echo "\nFirst Purchase:\n";
$evaluator->execute(clone $ruleSet2, $firstPurchaseContext); // Output: Log: Customer is not VIP, no discount applied., Log: Order amount is less than $100, no discount applied., Applied 5% discount: $3.75, Final price: $71.25, Message: First-time customer discount applied., No discount applied

echo "\nExcluded Item:\n";
$evaluator->execute(clone $ruleSet3, $excludedContext); // Output: Log: Customer is not VIP, no discount applied., Log: Order amount is less than $100, no discount applied., Log: No discount for returning customers., Log: No discount applicable due to low order amount or exclusion.
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
$builder->actions()->registerAction('addError', function(ArrayContext $context, string $field = '', string $message = 'Invalid value') {
    echo "Validation error: {$field} - {$message}\n";
    return true;
});

$builder->actions()->registerAction('log', function(ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
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
            'arguments' => [
                'field' => 'username',
                'message' => 'Username must be 3-20 characters and contain only letters, numbers, and underscores'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'Username validation passed',
            ],
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
            'arguments' => [
                'field' => 'email',
                'message' => 'Please enter a valid email address'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'Email validation passed',
            ],
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
            'arguments' => [
                'field' => 'password',
                'message' => 'Password must be at least 8 characters and include at least one uppercase letter and one number'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'log',
            'arguments' => [
                'message' => 'Password validation passed',
            ],
        ],
    ],
];

// Evaluate the rule set with the contexts
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Build the rule set
$ruleSet = $builder->build($config);
$ruleSet1 = $builder->build($config);

// Test with invalid form data
$invalidFormData = new ArrayContext([
    'username' => 'u$er',
    'email' => 'invalid-email',
    'password' => 'weak',
]);

// Execute validation
echo "Validating form data:\n";
$evaluator->execute(clone $ruleSet, $invalidFormData);
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
$evaluator->execute(clone $ruleSet1, $validFormData);
// Output:
// Log: Username validation passed
// Log: Email validation passed
// Log: Password validation passed
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
$builder->actions()->registerAction('transitionTo', function(ArrayContext $context, string $newState = '') {
    $currentState = $context->getField('currentState');

    echo "Transitioning from '{$currentState}' to '{$newState}'\n";
    return true;
});

$builder->actions()->registerAction('rejectTransition', function(ArrayContext $context, string $reason = 'Invalid transition') {
    $currentState = $context->getField('currentState');
    $requestedTransition = $context->getField('requestedTransition');

    echo "Rejected transition from '{$currentState}' to '{$requestedTransition}': {$reason}\n";
    return true;
});

$builder->actions()->registerAction('log', function(ArrayContext $context, string $message) {
    echo "Log: {$message}\n";
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
            'arguments' => [
                'newState' => 'review'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => [
                'reason' => 'Document must be complete before review'
            ],
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
            'arguments' => [
                'newState' => 'approved'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => [
                'reason' => 'Only managers can approve documents'
            ],
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
            'arguments' => [
                'newState' => 'rejected'
            ],
        ],
        'else' => [
            'node' => 'action',
            'actionName' => 'rejectTransition',
            'arguments' => [
                'reason' => 'Only managers or reviewers can reject documents'
            ],
        ],
    ],
];

// Evaluate the rule set with the contexts
$evaluator = new Maniaba\RuleEngine\Evaluators\BasicEvaluator();

// Build the rule set
$ruleSet = $builder->build($config);
$ruleSet1 = $builder->build($config);
$ruleSet2 = $builder->build($config);
$ruleSet3 = $builder->build($config);

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
$evaluator->execute(clone $ruleSet, $draftToReviewValid);
// Output: Transitioning from 'draft' to 'review'

echo "\nInvalid draft to review transition:\n";
$evaluator->execute(clone $ruleSet1, $draftToReviewInvalid);
// Output: Rejected transition from 'draft' to 'review': Document must be complete before review

echo "\nValid review to approved transition:\n";
$evaluator->execute(clone $ruleSet2, $reviewToApprovedValid);
// Output: Transitioning from 'review' to 'approved'

echo "\nInvalid review to approved transition:\n";
$evaluator->execute(clone $ruleSet3, $reviewToApprovedInvalid);
// Output: Rejected transition from 'review' to 'approved': Only managers can approve documents
```

## Next Steps

These examples demonstrate some common use cases for the Rule Engine. You can adapt and extend these examples to fit your specific requirements. For more advanced usage, see the [Advanced Usage](advanced-usage.md) guide.
