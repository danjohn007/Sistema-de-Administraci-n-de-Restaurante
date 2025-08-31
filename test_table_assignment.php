<?php
// Test script for table assignment in public orders
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

echo "=== Testing Table Assignment in Public Orders ===\n";

// Test 1: Check if tables are loading properly for public orders
echo "\n1. Testing table availability for public orders...\n";

try {
    $tableModel = new Table();
    $availableTables = $tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
    
    echo "  ✓ Found " . count($availableTables) . " available tables\n";
    
    if (count($availableTables) > 0) {
        foreach ($availableTables as $table) {
            echo "    - Mesa {$table['number']} (ID: {$table['id']}, Status: {$table['status']})\n";
        }
    } else {
        echo "  ⚠ No available tables found\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error loading tables: " . $e->getMessage() . "\n";
}

// Test 2: Check table_id validation in public orders
echo "\n2. Testing table_id validation in public orders...\n";

$publicController = new PublicController();

// Test with no table_id (should be valid)
echo "  Testing order without table_id...\n";
$testData1 = [
    'customer_name' => 'Test Customer',
    'customer_phone' => '1234567890',
    'notes' => 'Test order'
];

// Use reflection to test the private validation method
$reflection = new ReflectionClass($publicController);
$validateMethod = $reflection->getMethod('validatePublicOrderInput');
$validateMethod->setAccessible(true);

$errors1 = $validateMethod->invoke($publicController, $testData1);
if (isset($errors1['table_id'])) {
    echo "    ✗ table_id should be optional but validation failed: " . $errors1['table_id'] . "\n";
} else {
    echo "    ✓ table_id is properly optional\n";
}

// Test with valid table_id
if (count($availableTables) > 0) {
    echo "  Testing order with valid table_id...\n";
    $testData2 = [
        'customer_name' => 'Test Customer',
        'customer_phone' => '1234567890',
        'table_id' => $availableTables[0]['id'],
        'notes' => 'Test order'
    ];
    
    $errors2 = $validateMethod->invoke($publicController, $testData2);
    if (isset($errors2['table_id'])) {
        echo "    ✗ Valid table_id failed validation: " . $errors2['table_id'] . "\n";
    } else {
        echo "    ✓ Valid table_id passed validation\n";
    }
}

// Test 3: Check table status update logic
echo "\n3. Testing table status update logic...\n";

try {
    $orderModel = new Order();
    
    // Check if the createPublicOrderWithCustomer method exists
    if (method_exists($orderModel, 'createPublicOrderWithCustomer')) {
        echo "  ✓ createPublicOrderWithCustomer method exists\n";
    } else {
        echo "  ✗ createPublicOrderWithCustomer method missing\n";
    }
    
    // Check table status update logic in PublicController
    $publicControllerFile = file_get_contents('controllers/PublicController.php');
    if (strpos($publicControllerFile, 'TABLE_OCCUPIED') !== false) {
        echo "  ✓ Table status update logic exists\n";
    } else {
        echo "  ✗ Table status update logic missing\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error testing order creation: " . $e->getMessage() . "\n";
}

// Test 4: Check database schema for table_id nullable
echo "\n4. Testing database schema for nullable table_id...\n";

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    $tableIdColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'table_id') {
            $tableIdColumn = $column;
            break;
        }
    }
    
    if ($tableIdColumn) {
        if ($tableIdColumn['Null'] === 'YES') {
            echo "  ✓ table_id column is nullable in database\n";
        } else {
            echo "  ✗ table_id column is NOT NULL in database - this could cause issues\n";
            echo "    Run migration: database/migration_optional_table_id.sql\n";
        }
        echo "    Current definition: {$tableIdColumn['Type']} {$tableIdColumn['Null']} {$tableIdColumn['Default']}\n";
    } else {
        echo "  ✗ table_id column not found in orders table\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error checking database schema: " . $e->getMessage() . "\n";
}

echo "\n=== Table Assignment Test Complete ===\n";
?>