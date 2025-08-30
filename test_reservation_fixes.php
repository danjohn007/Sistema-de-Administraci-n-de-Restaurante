<?php
/**
 * Test script for reservation and order editing fixes
 * Run this from command line: php test_reservation_fixes.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Sistema de Restaurante - Reservation & Order Fixes Tests ===\n\n";

// Define BASE_PATH for config
define('BASE_PATH', __DIR__);

echo "1. Testing configuration and dependencies...\n";
require_once 'config/config.php';

echo "  ✓ Configuration loaded\n";
echo "  ✓ User roles defined: ADMIN=" . ROLE_ADMIN . ", WAITER=" . ROLE_WAITER . ", CASHIER=" . ROLE_CASHIER . "\n";

// Test 2: File structure for reservations
echo "\n2. Testing reservation file structure...\n";

$requiredFiles = [
    'views/reservations/create.php',
    'views/reservations/edit.php',
    'views/reservations/index.php',
    'views/reservations/view.php',
    'controllers/ReservationsController.php',
    'models/Reservation.php',
    'database/migration_multiple_tables.sql'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "  ✓ $file exists\n";
    } else {
        echo "  ✗ $file missing\n";
    }
}

// Test 3: Class loading
echo "\n3. Testing class autoloading...\n";

try {
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

    $classes = ['ReservationsController', 'Reservation', 'Order', 'OrderItem', 'Waiter'];
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "  ✓ $class loaded successfully\n";
        } else {
            echo "  ✗ $class failed to load\n";
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error loading classes: " . $e->getMessage() . "\n";
}

// Test 4: Database migration syntax
echo "\n4. Testing database migration syntax...\n";

$migrationFile = 'database/migration_multiple_tables.sql';
if (file_exists($migrationFile)) {
    $sql = file_get_contents($migrationFile);
    
    // Check for key components
    $checks = [
        'CREATE TABLE IF NOT EXISTS reservation_tables' => 'reservation_tables table creation',
        'ALTER TABLE reservations' => 'reservations table modification',
        'ADD COLUMN waiter_id' => 'waiter_id column addition',
        'FOREIGN KEY (waiter_id) REFERENCES waiters(id)' => 'waiter foreign key',
        'INSERT INTO reservation_tables' => 'data migration'
    ];
    
    foreach ($checks as $pattern => $description) {
        if (strpos($sql, $pattern) !== false) {
            echo "  ✓ $description found in migration\n";
        } else {
            echo "  ⚠ $description not found in migration\n";
        }
    }
} else {
    echo "  ✗ Migration file not found\n";
}

// Test 5: Reservation model method verification
echo "\n5. Testing Reservation model methods...\n";

if (class_exists('Reservation')) {
    $reflection = new ReflectionClass('Reservation');
    $methods = [
        'getReservationsWithTables' => 'Get reservations with table info',
        'checkTableAvailability' => 'Check table availability',
        'createReservationWithCustomer' => 'Create reservation with customer',
        'addTablesToReservation' => 'Add tables to reservation',
        'getReservationTables' => 'Get reservation tables',
        'updateReservationWithTables' => 'Update reservation with tables'
    ];
    
    foreach ($methods as $method => $description) {
        if ($reflection->hasMethod($method)) {
            echo "  ✓ $method - $description\n";
        } else {
            echo "  ✗ $method missing - $description\n";
        }
    }
}

// Test 6: Order editing functionality
echo "\n6. Testing Order model methods for editing...\n";

if (class_exists('Order')) {
    $reflection = new ReflectionClass('Order');
    $methods = [
        'addItemToOrder' => 'Add item to order',
        'removeItemFromOrder' => 'Remove item from order',
        'updateOrderTotal' => 'Update order total',
        'getOrderItems' => 'Get order items'
    ];
    
    foreach ($methods as $method => $description) {
        if ($reflection->hasMethod($method)) {
            echo "  ✓ $method - $description\n";
        } else {
            echo "  ✗ $method missing - $description\n";
        }
    }
}

// Test 7: View file content validation
echo "\n7. Testing view file content...\n";

$viewTests = [
    'views/reservations/create.php' => [
        'table_ids[]' => 'Multiple table selection checkboxes',
        'waiter_id' => 'Waiter assignment dropdown',
        'table-checkbox' => 'Table checkbox CSS class',
        'updateCapacityCounter' => 'JavaScript capacity counter'
    ],
    'views/reservations/edit.php' => [
        'table_ids[]' => 'Multiple table selection checkboxes',
        'waiter_id' => 'Waiter assignment dropdown',
        'reservationTables' => 'Existing table assignments'
    ],
    'views/orders/edit.php' => [
        'removeItem(' => 'Item removal function',
        'new_items[' => 'New items addition',
        'btn-minus' => 'Quantity decrease button',
        'btn-plus' => 'Quantity increase button'
    ]
];

foreach ($viewTests as $file => $tests) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($tests as $pattern => $description) {
            if (strpos($content, $pattern) !== false) {
                echo "  ✓ $file: $description\n";
            } else {
                echo "  ⚠ $file: $description not found\n";
            }
        }
    } else {
        echo "  ✗ $file: File not found\n";
    }
}

echo "\n=== Test Summary ===\n";
echo "✓ = Working correctly\n";
echo "⚠ = Potential issue or missing feature\n";
echo "✗ = Problem that needs fixing\n\n";

echo "Manual Testing Recommendations:\n";
echo "1. Run database migration: database/migration_multiple_tables.sql\n";
echo "2. Test reservation creation with multiple tables\n";
echo "3. Test waiter assignment in reservations\n";
echo "4. Test order item addition/removal\n";
echo "5. Verify all user roles can access reservations\n";
echo "6. Check that table assignment works in order editing\n\n";

echo "Next Steps:\n";
echo "- Apply database migration\n";
echo "- Test with actual web interface\n";
echo "- Verify user permissions work correctly\n";
echo "- Test edge cases (no tables selected, invalid data, etc.)\n";