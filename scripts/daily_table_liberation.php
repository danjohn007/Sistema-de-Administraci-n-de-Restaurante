#!/usr/bin/env php
<?php
/**
 * Daily Table Liberation Script
 * This script should be run daily at 5:00 AM and 6:00 AM to automatically liberate tables:
 * - At 5:00 AM: Liberate tables with orders from previous days that haven't been ticketed
 * - At 6:00 AM: Liberate ALL tables to start the new day fresh
 * 
 * To set up as cron jobs, add these lines to your crontab:
 * 0 5 * * * /path/to/php /path/to/your/project/scripts/daily_table_liberation.php expired
 * 0 6 * * * /path/to/php /path/to/your/project/scripts/daily_table_liberation.php all
 */

// Set the base path
define('BASE_PATH', dirname(__DIR__));
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

echo "=== Daily Table Liberation Script ===\n";
echo "Starting at: " . date('Y-m-d H:i:s') . "\n";

// Check command line argument for liberation type
$liberationType = isset($argv[1]) ? $argv[1] : 'all';
echo "Liberation type: " . ($liberationType === 'expired' ? 'Expired orders only' : 'All tables') . "\n\n";

try {
    // Initialize models
    $tableModel = new Table();
    $orderModel = new Order();
    
    // 1. Get tables with pending orders from previous days
    echo "1. Checking for tables with expired orders...\n";
    $tablesWithExpiredOrders = $tableModel->getTablesWithPendingOrders();
    
    if (!empty($tablesWithExpiredOrders)) {
        echo "   Found " . count($tablesWithExpiredOrders) . " tables with expired orders:\n";
        foreach ($tablesWithExpiredOrders as $table) {
            echo "   - Mesa {$table['table_number']}: {$table['pending_orders_count']} pedidos pendientes, monto total: $" . number_format($table['total_pending_amount'], 2) . "\n";
        }
    } else {
        echo "   No tables with expired orders found.\n";
    }
    
    // 2. Liberate tables based on type
    if ($liberationType === 'expired') {
        echo "\n2. Liberating tables with expired orders (from previous days)...\n";
        $liberationResult = $tableModel->liberateExpiredTables();
    } else {
        echo "\n2. Liberating all tables...\n";
        $liberationResult = $tableModel->liberateTablesDaily();
    }
    
    if ($liberationResult) {
        echo "   ✓ Tables have been liberated successfully.\n";
    } else {
        echo "   ✗ Error liberating tables.\n";
    }
    
    // 3. Get expired orders count
    echo "\n3. Checking for expired orders...\n";
    $expiredOrdersCount = $orderModel->getExpiredOrdersCount();
    
    if ($expiredOrdersCount > 0) {
        echo "   Found {$expiredOrdersCount} expired orders from previous days.\n";
        echo "   These orders will appear in the 'Pedidos Vencidos' section of the dashboard.\n";
        
        // Optionally, you could send an email notification here
        // sendExpiredOrdersNotification($expiredOrdersCount);
    } else {
        echo "   No expired orders found.\n";
    }
    
    echo "\n=== Table Liberation Completed Successfully ===\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

/**
 * Optional function to send email notifications about expired orders
 * You can implement this based on your email setup
 */
function sendExpiredOrdersNotification($count) {
    // Example implementation:
    // $to = 'admin@restaurant.com';
    // $subject = 'Alerta: Pedidos Vencidos - ' . date('Y-m-d');
    // $message = "Se encontraron {$count} pedidos vencidos que requieren atención.";
    // mail($to, $subject, $message);
    
    echo "   Email notification would be sent here (implement sendExpiredOrdersNotification function)\n";
}
?>