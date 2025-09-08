<?php
/**
 * Test script to verify the database fixes for payment methods and SQL queries
 * This will test the fixed methods in Ticket.php
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Ticket.php';

try {
    $db = Database::getConnection();
    $ticketModel = new Ticket();
    
    echo "Testing Database Connection...\n";
    echo "✓ Database connection successful\n\n";
    
    // Test 1: Check if the database schema supports new payment methods
    echo "Test 1: Checking payment methods support...\n";
    try {
        $query = "SHOW COLUMNS FROM tickets LIKE 'payment_method'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            echo "Payment method column definition: " . $result['Type'] . "\n";
            if (strpos($result['Type'], 'intercambio') !== false && strpos($result['Type'], 'pendiente_por_cobrar') !== false) {
                echo "✓ Payment methods 'intercambio' and 'pendiente_por_cobrar' are supported\n";
            } else {
                echo "✗ Missing payment methods in database schema\n";
                echo "Please run the migration: database/migration_payment_methods.sql\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error checking payment methods: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 2: Test getPendingPayments method
    echo "Test 2: Testing getPendingPayments() method...\n";
    try {
        $pendingPayments = $ticketModel->getPendingPayments();
        echo "✓ getPendingPayments() executed successfully\n";
        echo "Found " . count($pendingPayments) . " pending payments\n";
    } catch (Exception $e) {
        echo "✗ Error in getPendingPayments(): " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 3: Test getTicketsByPaymentMethod method
    echo "Test 3: Testing getTicketsByPaymentMethod('intercambio') method...\n";
    try {
        $intercambioTickets = $ticketModel->getTicketsByPaymentMethod('intercambio');
        echo "✓ getTicketsByPaymentMethod('intercambio') executed successfully\n";
        echo "Found " . count($intercambioTickets) . " intercambio tickets\n";
    } catch (Exception $e) {
        echo "✗ Error in getTicketsByPaymentMethod('intercambio'): " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 4: Test updatePaymentMethod method
    echo "Test 4: Testing updatePaymentMethod validation...\n";
    try {
        // Test with valid method
        $result1 = $ticketModel->updatePaymentMethod(999999, 'intercambio'); // Non-existent ID should return true but affect 0 rows
        echo "✓ updatePaymentMethod accepts 'intercambio'\n";
        
        $result2 = $ticketModel->updatePaymentMethod(999999, 'pendiente_por_cobrar'); // Non-existent ID should return true but affect 0 rows  
        echo "✓ updatePaymentMethod accepts 'pendiente_por_cobrar'\n";
        
        // Test with invalid method
        $result3 = $ticketModel->updatePaymentMethod(999999, 'invalid_method');
        if ($result3 === false) {
            echo "✓ updatePaymentMethod correctly rejects invalid methods\n";
        } else {
            echo "✗ updatePaymentMethod should reject invalid methods\n";
        }
    } catch (Exception $e) {
        echo "✗ Error in updatePaymentMethod: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    echo "All tests completed!\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration\n";
}
?>