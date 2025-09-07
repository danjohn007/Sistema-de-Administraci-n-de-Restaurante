<?php
/**
 * Test script to check birthday database structure
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Birthday Database Structure Test ===\n\n";

// Define BASE_PATH for config
define('BASE_PATH', __DIR__);

// Load configuration
require_once 'config/config.php';

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✓ Database connection established\n\n";
    
    // Check customers table structure
    echo "1. Checking customers table structure...\n";
    $stmt = $pdo->query("DESCRIBE customers");
    $columns = $stmt->fetchAll();
    
    $birthdayColumns = [];
    foreach ($columns as $column) {
        if (strpos($column['Field'], 'birthday') !== false) {
            $birthdayColumns[] = $column;
            echo "  ✓ Found column: {$column['Field']} ({$column['Type']})\n";
        }
    }
    
    if (empty($birthdayColumns)) {
        echo "  ⚠ No birthday columns found\n";
    }
    
    // Check if new columns exist
    echo "\n2. Checking for new birthday columns...\n";
    $hasNewColumns = false;
    foreach ($birthdayColumns as $column) {
        if ($column['Field'] === 'birthday_day' || $column['Field'] === 'birthday_month') {
            echo "  ✓ New column exists: {$column['Field']}\n";
            $hasNewColumns = true;
        }
    }
    
    if (!$hasNewColumns) {
        echo "  ⚠ New birthday columns (birthday_day, birthday_month) not found\n";
        echo "  ⚠ Need to apply the database migration mentioned in problem statement\n";
    }
    
    // Check current birthday data format
    echo "\n3. Checking current birthday data...\n";
    $stmt = $pdo->query("SELECT id, name, birthday, birthday_day, birthday_month FROM customers WHERE birthday IS NOT NULL LIMIT 5");
    $customers = $stmt->fetchAll();
    
    if (empty($customers)) {
        echo "  ⚠ No customers with birthday data found\n";
    } else {
        foreach ($customers as $customer) {
            echo "  Customer: {$customer['name']}\n";
            echo "    birthday: " . ($customer['birthday'] ?? 'NULL') . "\n";
            echo "    birthday_day: " . ($customer['birthday_day'] ?? 'NULL') . "\n";
            echo "    birthday_month: " . ($customer['birthday_month'] ?? 'NULL') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>