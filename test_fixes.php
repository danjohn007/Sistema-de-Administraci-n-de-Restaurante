<?php
// Test script for the restaurant system fixes
// This script tests the key functionality that was fixed

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/BaseModel.php';
require_once 'models/Order.php';
require_once 'models/Reservation.php';
require_once 'models/Customer.php';
require_once 'models/OrderItem.php';

echo "Testing Restaurant System Fixes\n";
echo "================================\n\n";

// Test 1: Order creation without table_id
echo "Test 1: Creating order without table assignment...\n";
try {
    $orderModel = new Order();
    $customerModel = new Customer();
    
    // Create test customer
    $customerData = [
        'name' => 'Test Customer',
        'phone' => '1234567890',
        'birthday' => '15/03' // New format DD/MM
    ];
    
    // Create order without table_id
    $orderData = [
        'table_id' => null, // No table assigned
        'waiter_id' => null,
        'status' => 'pendiente_confirmacion',
        'notes' => 'Test order without table',
        'customer_name' => 'Test Customer',
        'customer_phone' => '1234567890',
        'is_pickup' => 1,
        'pickup_datetime' => date('Y-m-d H:i:s', strtotime('+1 hour'))
    ];
    
    $items = [
        [
            'dish_id' => 1,
            'quantity' => 2,
            'unit_price' => 10.50,
            'notes' => 'Extra sauce'
        ]
    ];
    
    // This should work without transaction errors
    $orderId = $orderModel->createPublicOrderWithCustomer($orderData, $items, $customerData);
    echo "✓ Order created successfully with ID: $orderId\n";
    
} catch (Exception $e) {
    echo "✗ Error creating order: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Reservation creation without table_id
echo "Test 2: Creating reservation without table preference...\n";
try {
    $reservationModel = new Reservation();
    
    $reservationData = [
        'table_id' => null, // No table preference
        'reservation_datetime' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        'party_size' => 4,
        'notes' => 'Test reservation without table preference',
        'status' => 'pendiente'
    ];
    
    $customerData = [
        'name' => 'Test Customer 2',
        'phone' => '0987654321',
        'birthday' => '25/12' // New format DD/MM
    ];
    
    // This should work without customer_id column errors
    $reservationId = $reservationModel->createReservationWithCustomer($reservationData, $customerData);
    echo "✓ Reservation created successfully with ID: $reservationId\n";
    
} catch (Exception $e) {
    echo "✗ Error creating reservation: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Birthday format validation
echo "Test 3: Testing birthday format validation...\n";

function validateBirthday($birthday) {
    if (empty($birthday)) return true;
    
    if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])$/', $birthday)) {
        return false;
    }
    
    list($day, $month) = explode('/', $birthday);
    return checkdate($month, $day, 2000);
}

$testBirthdays = [
    '15/03' => true,  // Valid
    '25/12' => true,  // Valid
    '31/02' => false, // Invalid date
    '32/01' => false, // Invalid day
    '15/13' => false, // Invalid month
    '5/3' => false,   // Wrong format
    '15-03' => false, // Wrong separator
    '' => true        // Empty (optional)
];

foreach ($testBirthdays as $birthday => $expected) {
    $result = validateBirthday($birthday);
    $status = ($result === $expected) ? '✓' : '✗';
    echo "$status Birthday '$birthday': " . ($result ? 'Valid' : 'Invalid') . "\n";
}

echo "\n";
echo "Test completed!\n";
?>