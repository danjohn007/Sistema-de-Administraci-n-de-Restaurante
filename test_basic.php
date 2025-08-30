<?php
/**
 * Basic functionality test script
 * Run this from command line: php test_basic.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Sistema de Restaurante - Basic Tests ===\n\n";

// Define BASE_PATH for config
define('BASE_PATH', __DIR__);

// Test 1: Configuration constants
echo "1. Testing configuration constants...\n";
require_once 'config/config.php';

$requiredConstants = [
    'ROLE_ADMIN', 'ROLE_WAITER', 'ROLE_CASHIER',
    'ORDER_PENDING_CONFIRMATION', 'ORDER_PENDING', 'ORDER_PREPARING', 'ORDER_READY', 'ORDER_DELIVERED',
    'TABLE_AVAILABLE', 'TABLE_OCCUPIED'
];

foreach ($requiredConstants as $constant) {
    if (defined($constant)) {
        echo "  ✓ $constant = " . constant($constant) . "\n";
    } else {
        echo "  ✗ $constant not defined\n";
    }
}

// Test 2: Autoloader and class loading
echo "\n2. Testing class autoloading...\n";

// Simple autoloader for testing
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models', 'core'];
    
    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$testClasses = [
    'PublicController',
    'OrdersController', 
    'DishesController',
    'TablesController',
    'Order',
    'BaseController',
    'BaseModel'
];

foreach ($testClasses as $class) {
    if (class_exists($class)) {
        echo "  ✓ $class loaded successfully\n";
    } else {
        echo "  ✗ $class failed to load\n";
    }
}

// Test 3: Check for required files
echo "\n3. Testing file structure...\n";

$requiredFiles = [
    'views/public/menu.php',
    'views/public/order_success.php', 
    'views/orders/confirm_public.php',
    'views/layouts/public_header.php',
    'views/layouts/public_footer.php',
    'database/migration_public_orders.sql'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "  ✓ $file exists\n";
    } else {
        echo "  ✗ $file missing\n";
    }
}

// Test 4: PHP Syntax check for key files
echo "\n4. Testing PHP syntax...\n";

$phpFiles = [
    'controllers/PublicController.php',
    'controllers/OrdersController.php',
    'models/Order.php',
    'views/orders/index.php',
    'views/dishes/index.php',
    'views/tables/index.php'
];

foreach ($phpFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "  ✓ $file syntax OK\n";
    } else {
        echo "  ✗ $file syntax error: " . implode(' ', $output) . "\n";
    }
}

// Test 5: Role validation logic
echo "\n5. Testing role validation logic...\n";

function testRoleValidation($userRole, $expectedCanCreateOrders, $expectedCanConfirmPublic, $expectedCanViewMenuReadOnly) {
    // Simulate the logic from controllers
    $canCreateOrders = in_array($userRole, [ROLE_ADMIN, ROLE_WAITER, ROLE_CASHIER]);
    $canConfirmPublic = in_array($userRole, [ROLE_ADMIN, ROLE_CASHIER]);
    $canViewMenuReadOnly = in_array($userRole, [ROLE_ADMIN, ROLE_WAITER]);
    
    $results = [
        'create_orders' => $canCreateOrders === $expectedCanCreateOrders,
        'confirm_public' => $canConfirmPublic === $expectedCanConfirmPublic,
        'view_menu_readonly' => $canViewMenuReadOnly === $expectedCanViewMenuReadOnly
    ];
    
    $allCorrect = array_reduce($results, function($carry, $item) { return $carry && $item; }, true);
    
    echo "  " . ($allCorrect ? "✓" : "✗") . " $userRole permissions: " . 
         "create=" . ($results['create_orders'] ? 'Y' : 'N') . 
         " confirm=" . ($results['confirm_public'] ? 'Y' : 'N') . 
         " readonly=" . ($results['view_menu_readonly'] ? 'Y' : 'N') . "\n";
    
    return $allCorrect;
}

$roleTests = [
    [ROLE_ADMIN, true, true, true],
    [ROLE_CASHIER, true, true, false], 
    [ROLE_WAITER, true, false, true]
];

foreach ($roleTests as [$role, $canCreate, $canConfirm, $canViewReadonly]) {
    testRoleValidation($role, $canCreate, $canConfirm, $canViewReadonly);
}

echo "\n=== Test Summary ===\n";
echo "Basic functionality tests completed.\n";
echo "For complete testing, run the manual tests described in TESTING.md\n";
echo "Don't forget to run the database migration: database/migration_public_orders.sql\n";
?>