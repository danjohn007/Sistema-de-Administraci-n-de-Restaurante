# Manual Testing Guide: Customer Registration Functionality

## Overview
This guide provides step-by-step manual testing procedures to verify that customer registration works correctly for both public and internal orders, ensuring proper database integration without SQLite dependencies.

## Prerequisites
- Restaurant system installed and running
- Database properly configured (MySQL/MariaDB recommended)
- Test users created (admin, cashier, waiter roles)
- Sample menu items available

## Test Environment Setup

### 1. Database Verification
Before testing, verify the database schema includes customer functionality:

```sql
-- Check customers table exists
DESCRIBE customers;

-- Check orders table has customer_id field
DESCRIBE orders;

-- Verify foreign key relationship
SHOW CREATE TABLE orders;
```

Expected results:
- `customers` table with fields: id, name, phone (UNIQUE), email, birthday, total_visits, total_spent
- `orders` table with `customer_id` field
- Foreign key relationship between orders.customer_id and customers.id

## Test Cases

### Test Group 1: Public Order Customer Registration

#### Test 1.1: New Customer Registration via Public Menu
**Objective:** Verify new customers are automatically registered when placing public orders

**Steps:**
1. Navigate to `/public/menu` (no login required)
2. Select dishes and add to order
3. Fill customer information:
   - Name: "Juan Pérez Test"
   - Phone: "555-TEST-001"
   - Birthday: "15/03" (optional)
4. Select table (optional)
5. Submit order

**Expected Results:**
- Order created with status "Pendiente Confirmación"
- New customer record created in database
- Customer linked to order via customer_id
- Success page displays order number

**Verification:**
```sql
-- Check customer was created
SELECT * FROM customers WHERE phone = '555-TEST-001';

-- Check order has customer_id
SELECT id, customer_id, customer_name, customer_phone, status 
FROM orders WHERE customer_phone = '555-TEST-001';
```

#### Test 1.2: Existing Customer Update via Public Menu
**Objective:** Verify existing customers are found by phone and optionally updated

**Steps:**
1. Navigate to `/public/menu`
2. Select dishes and add to order
3. Fill customer information:
   - Name: "Juan Carlos Pérez Test" (updated name)
   - Phone: "555-TEST-001" (same as Test 1.1)
   - Birthday: "15/03"
4. Submit order

**Expected Results:**
- Order created successfully
- Existing customer found by phone
- Customer name updated if different
- New order linked to existing customer

**Verification:**
```sql
-- Check customer name was updated
SELECT name, phone FROM customers WHERE phone = '555-TEST-001';

-- Check both orders linked to same customer
SELECT COUNT(*) as order_count FROM orders WHERE customer_phone = '555-TEST-001';
```

#### Test 1.3: Public Order Input Validation
**Objective:** Verify proper validation of customer data

**Test Cases:**
1. **Missing Name:**
   - Leave name field empty
   - Expected: Validation error "Nombre del cliente es requerido"

2. **Missing Phone:**
   - Leave phone field empty
   - Expected: Validation error "Teléfono del cliente es requerido"

3. **Invalid Birthday Format:**
   - Enter birthday as "15/13" (invalid month)
   - Expected: Validation error "Fecha de cumpleaños inválida"

4. **Valid Data:**
   - Name: "María García"
   - Phone: "555-TEST-002"
   - Birthday: "20/12"
   - Expected: Order created successfully

### Test Group 2: Internal Order Customer Registration

#### Test 2.1: Customer Search in Order Creation
**Objective:** Verify customer search functionality in admin/cashier order creation

**Steps:**
1. Login as admin or cashier
2. Navigate to `/orders/create`
3. In customer section, search for "Juan" (from Test 1.1)
4. Select found customer
5. Complete order with table and dishes
6. Submit order

**Expected Results:**
- Customer search returns matching results
- Selected customer populates form
- Order created with existing customer_id
- No duplicate customer record created

#### Test 2.2: New Customer Creation in Order Form
**Objective:** Verify new customer creation during internal order process

**Steps:**
1. Login as admin or cashier
2. Navigate to `/orders/create`
3. Expand "Nuevo Cliente" section
4. Fill new customer form:
   - Name: "Ana Rodríguez"
   - Phone: "555-TEST-003"
5. Complete order details
6. Submit order

**Expected Results:**
- New customer created automatically
- Order linked to new customer
- Customer appears in future searches

**Verification:**
```sql
-- Verify customer was created
SELECT * FROM customers WHERE phone = '555-TEST-003';

-- Verify order has customer_id
SELECT customer_id FROM orders WHERE customer_phone = '555-TEST-003';
```

#### Test 2.3: Customer Data Validation in Internal Orders
**Objective:** Verify validation works in internal order creation

**Test Cases:**
1. **Empty Customer Fields:**
   - Try to create customer with empty name/phone
   - Expected: Error "Nombre y teléfono del cliente son requeridos"

2. **Duplicate Phone Prevention:**
   - Try to create customer with existing phone "555-TEST-001"
   - Expected: System finds existing customer instead of creating duplicate

### Test Group 3: Customer Statistics and Analytics

#### Test 3.1: Customer Statistics Update
**Objective:** Verify customer statistics update when orders are completed

**Steps:**
1. Find an order from previous tests
2. Update order status to "Entregado" (delivered)
3. Check customer statistics

**Expected Results:**
- Customer total_visits incremented by 1
- Customer total_spent increased by order total

**Verification:**
```sql
-- Check customer stats were updated
SELECT name, phone, total_visits, total_spent 
FROM customers WHERE phone = '555-TEST-001';
```

#### Test 3.2: Customer Search and Analytics
**Objective:** Verify customer search and analytics features

**Steps:**
1. Navigate to customer analytics/best diners section
2. Search for customers by name/phone
3. View customer details and order history

**Expected Results:**
- Search returns relevant customers
- Customer details show accurate statistics
- Order history displays correctly

### Test Group 4: Error Handling and Edge Cases

#### Test 4.1: Database Connection Error Handling
**Objective:** Verify graceful handling of database errors

**Steps:**
1. Temporarily simulate database connection issue
2. Attempt to create order with new customer
3. Verify error handling

**Expected Results:**
- User-friendly error message displayed
- No partial data corruption
- System remains stable

#### Test 4.2: Long Input Handling
**Objective:** Verify handling of very long customer names/phones

**Test Cases:**
1. **Very Long Name:**
   - Enter 300+ character name
   - Expected: Validation error or truncation

2. **Invalid Phone Format:**
   - Enter phone with special characters "555-@#$-001"
   - Expected: Validation error

#### Test 4.3: Unicode and Special Characters
**Objective:** Verify proper handling of international names

**Steps:**
1. Create customer with name "José María Ñuñez"
2. Create order and verify data integrity

**Expected Results:**
- Unicode characters stored correctly
- Names display properly in all views

### Test Group 5: Integration Testing

#### Test 5.1: Public to Internal Order Flow
**Objective:** Verify seamless customer data flow between public and internal orders

**Steps:**
1. Create public order with new customer
2. Admin confirms public order (assign waiter)
3. Create new internal order with same customer
4. Verify customer data consistency

**Expected Results:**
- Same customer found by phone in both flows
- Customer data consistent across all orders
- No duplicate customer records

#### Test 5.2: Customer History Tracking
**Objective:** Verify complete customer order history tracking

**Steps:**
1. Create multiple orders for same customer across different flows
2. View customer details/history
3. Verify all orders appear in customer history

**Expected Results:**
- All orders linked to customer
- Order history shows chronological order
- Statistics reflect all customer activity

## Verification Queries

Use these SQL queries to verify test results:

```sql
-- List all test customers
SELECT * FROM customers WHERE phone LIKE '555-TEST-%';

-- List all orders with customer information
SELECT o.id, o.status, o.total, c.name, c.phone, c.total_visits, c.total_spent
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
WHERE c.phone LIKE '555-TEST-%';

-- Check customer statistics accuracy
SELECT 
    c.name,
    c.phone,
    c.total_visits,
    c.total_spent,
    COUNT(o.id) as actual_orders,
    SUM(CASE WHEN o.status = 'entregado' THEN o.total ELSE 0 END) as actual_spent
FROM customers c
LEFT JOIN orders o ON c.id = o.customer_id
WHERE c.phone LIKE '555-TEST-%'
GROUP BY c.id;
```

## Cleanup

After testing, clean up test data:

```sql
-- Remove test orders
DELETE FROM order_items WHERE order_id IN (
    SELECT id FROM orders WHERE customer_phone LIKE '555-TEST-%'
);
DELETE FROM orders WHERE customer_phone LIKE '555-TEST-%';

-- Remove test customers
DELETE FROM customers WHERE phone LIKE '555-TEST-%';
```

## Success Criteria

✅ **All tests pass if:**
- Customers are automatically registered for both public and internal orders
- Phone number uniquely identifies customers (no duplicates)
- Customer data validation prevents invalid input
- Customer statistics update correctly on order completion
- System works with MySQL/MariaDB (no SQLite dependency)
- Error handling provides clear user feedback
- Unicode and special characters are handled properly
- Customer search functionality works correctly
- Order history tracking is accurate

## Notes

- Test with different browsers for public orders
- Verify mobile responsiveness of public menu
- Test with concurrent users to ensure no race conditions
- Monitor database performance with multiple customer operations
- Verify backup and restore procedures include customer data