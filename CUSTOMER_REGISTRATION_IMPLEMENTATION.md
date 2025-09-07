# Customer Registration Implementation Documentation

## Overview

This document outlines the complete implementation of customer registration functionality for the restaurant management system. The implementation ensures that customers are correctly registered in the database when placing orders, both through public orders and internal orders, with proper validation and error handling.

## Architecture

### Database Schema

The customer registration system is built on the following database structure:

```sql
-- Customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,  -- Unique identifier
    email VARCHAR(255) NULL,
    birthday VARCHAR(10) NULL,          -- Format: DD/MM
    total_visits INT DEFAULT 0,
    total_spent DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE
);

-- Orders table (relevant fields)
CREATE TABLE orders (
    -- ... other fields ...
    customer_id INT NULL,               -- Links to customers.id
    customer_name VARCHAR(255) NULL,    -- Backup/legacy field
    customer_phone VARCHAR(20) NULL,    -- Backup/legacy field
    -- ... other fields ...
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);
```

### Core Components

#### 1. Customer Model (`models/Customer.php`)

**Key Methods:**
- `findOrCreateByPhone($customerData)` - Primary method for customer registration
- `searchCustomers($query)` - Search customers by name or phone
- `updateStats($customerId, $orderTotal)` - Update customer statistics

**Implementation Details:**
```php
public function findOrCreateByPhone($customerData) {
    // Try to find existing customer by phone
    $existing = $this->findBy('phone', $customerData['phone']);
    
    if ($existing) {
        // Update name if provided and different
        if (isset($customerData['name']) && $customerData['name'] !== $existing['name']) {
            $this->update($existing['id'], ['name' => $customerData['name']]);
        }
        return $existing['id'];
    }
    
    // Create new customer
    return $this->create($customerData);
}
```

#### 2. Order Model (`models/Order.php`)

**Key Methods:**
- `createPublicOrderWithCustomer($orderData, $items, $customerData)` - Handles public orders with customer registration
- `updateOrderStatusAndCustomerStats($orderId, $newStatus)` - Updates customer stats when order is completed

**Implementation Details:**
- Uses database transactions to ensure data consistency
- Automatically links orders to customers via `customer_id`
- Updates customer statistics when orders are delivered

#### 3. Public Controller (`controllers/PublicController.php`)

**Key Methods:**
- `processPublicOrder()` - Handles public order creation with customer registration

**Customer Data Collection:**
```php
$customerData = [
    'name' => $_POST['customer_name'],
    'phone' => $_POST['customer_phone'],
    'birthday' => !empty($_POST['customer_birthday']) ? $_POST['customer_birthday'] : null
];
```

#### 4. Orders Controller (`controllers/OrdersController.php`)

**Key Methods:**
- `processCreate()` - Handles internal order creation with customer assignment
- `searchCustomers()` - AJAX endpoint for customer search

**Customer Handling:**
- Supports both existing customer selection and new customer creation
- Validates customer data before creation
- Integrates with customer search functionality

## Implementation Features

### 1. Automatic Customer Registration

**Public Orders:**
- Customers provide name and phone when placing orders
- System automatically checks if customer exists by phone
- Creates new customer if not found, updates existing if found
- Links order to customer via `customer_id`

**Internal Orders:**
- Staff can search for existing customers
- Option to create new customers during order process
- Same `findOrCreateByPhone` logic ensures consistency

### 2. Phone Number as Primary Identifier

- Phone numbers are unique in the database
- Used as the primary method to identify returning customers
- Prevents duplicate customer records
- Allows name updates for existing customers

### 3. Data Validation

**Customer Data Validation:**
- Name: Required, max 255 characters
- Phone: Required, max 20 characters, format validation
- Email: Optional, format validation when provided
- Birthday: Optional, DD/MM format validation

**Error Handling:**
- Clear error messages for validation failures
- Graceful handling of database errors
- User-friendly feedback in all scenarios

### 4. Customer Statistics

**Automatic Updates:**
- `total_visits` incremented when order is delivered
- `total_spent` increased by order total
- Statistics used for customer analytics and reporting

### 5. Search Functionality

**Customer Search:**
- Search by name or phone number
- Case-insensitive matching
- Returns top 10 results ordered by visit frequency
- AJAX-powered real-time search in order creation

## Database Independence

The implementation is designed to work with any SQL database:

- Uses PDO for database abstraction
- No SQLite-specific features or syntax
- Compatible with MySQL, MariaDB, PostgreSQL
- Transactions ensure data consistency across databases

## Security Considerations

### 1. Input Validation
- All customer data validated before database operations
- Phone format validation prevents malicious input
- Length limits prevent buffer overflow attacks

### 2. SQL Injection Prevention
- Prepared statements used for all database queries
- Parameters properly escaped and bound
- No dynamic SQL construction with user input

### 3. Data Integrity
- Database transactions ensure consistency
- Foreign key constraints maintain referential integrity
- Proper error handling prevents partial data corruption

## Integration Points

### 1. Order Creation Flow

```
1. User provides customer information
2. System validates input data
3. findOrCreateByPhone() called
4. Customer ID returned or created
5. Order created with customer_id link
6. Transaction committed
```

### 2. Customer Analytics
- Customer data feeds into analytics dashboards
- Order history tracking for customer insights
- Statistics used for loyalty programs and reporting

### 3. Future Extensions
- Email integration ready (email field available)
- Birthday tracking for promotions
- Customer preferences can be added
- Loyalty points system can be integrated

## Testing

### Unit Tests
- Comprehensive test suite without database dependencies
- Mock objects simulate database operations
- Edge cases and security scenarios covered
- Validation logic thoroughly tested

### Manual Testing
- Complete manual testing guide provided
- Covers both public and internal order flows
- Database verification queries included
- Error scenarios and edge cases tested

## Deployment Considerations

### 1. Database Migration
Ensure the following fields exist in your orders table:
- `customer_id INT NULL`
- Foreign key constraint to customers table

### 2. Existing Data
- Legacy orders with `customer_name`/`customer_phone` remain functional
- New orders use `customer_id` for better data integrity
- Migration script can link existing orders to customers

### 3. Performance
- Indexes on `customers.phone` for fast lookups
- Customer statistics updated efficiently
- Search queries optimized for performance

## Configuration

No special configuration required. The system works with:
- Any PDO-compatible database
- Standard PHP installation
- No external dependencies

## Monitoring

Monitor the following for optimal performance:
- Customer creation rate
- Phone number uniqueness violations
- Search query performance
- Customer statistics accuracy

## Support

The customer registration system is fully implemented and tested. Key benefits:

✅ **Automatic customer registration** for both public and internal orders
✅ **Phone-based customer identification** prevents duplicates
✅ **Comprehensive validation** ensures data quality
✅ **Database independence** works with any SQL database
✅ **Security hardened** against common attacks
✅ **Fully tested** with unit and manual test suites
✅ **Analytics ready** with customer statistics tracking

The implementation meets all requirements and is ready for production use.