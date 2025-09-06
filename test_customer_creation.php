<?php
// Test customer creation functionality

// Define base path for the application
define('BASE_PATH', __DIR__);
define('BASE_URL', '/restaurante/');

// Include configuration
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models', 'core'];
    
    foreach ($directories as $directory) {
        $file = BASE_PATH . '/' . $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

echo "=== Customer Creation Test ===\n\n";

try {
    // Test customer creation
    $customer = new Customer();
    $testData = [
        'name' => 'Test Customer ' . date('Y-m-d H:i:s'),
        'phone' => '555-TEST-' . rand(1000, 9999)
    ];

    echo "1. Testing customer creation with data:\n";
    echo "   Name: " . $testData['name'] . "\n";
    echo "   Phone: " . $testData['phone'] . "\n";

    $result = $customer->create($testData);
    if ($result) {
        echo "   ✓ Customer created successfully with ID: " . $result . "\n\n";
        
        // Test findOrCreateByPhone with existing customer
        echo "2. Testing findOrCreateByPhone with existing customer:\n";
        $existingResult = $customer->findOrCreateByPhone($testData);
        echo "   Result: " . $existingResult . " (should be same as above)\n";
        echo "   ✓ Found existing customer\n\n";
        
        // Test findOrCreateByPhone with new customer
        echo "3. Testing findOrCreateByPhone with new customer:\n";
        $newTestData = [
            'name' => 'New Test Customer ' . date('Y-m-d H:i:s'),
            'phone' => '555-NEW-' . rand(1000, 9999)
        ];
        echo "   Name: " . $newTestData['name'] . "\n";
        echo "   Phone: " . $newTestData['phone'] . "\n";
        
        $newResult = $customer->findOrCreateByPhone($newTestData);
        if ($newResult) {
            echo "   ✓ New customer created with ID: " . $newResult . "\n\n";
            
            // Clean up new customer
            $customer->delete($newResult);
            echo "   ✓ New test customer deleted\n";
        } else {
            echo "   ✗ Failed to create new customer\n";
        }
        
        // Clean up original customer
        $customer->delete($result);
        echo "   ✓ Original test customer deleted\n\n";
        
    } else {
        echo "   ✗ Failed to create customer\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";
?>