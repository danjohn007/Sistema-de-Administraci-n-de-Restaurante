-- SQL statements for order and table operations testing
-- This file documents and tests the SQL operations used in the restaurant administration system
-- Run these statements in your MySQL database to verify operations

-- ============================================================================
-- TABLE AVAILABILITY AND RESERVATION OPERATIONS
-- ============================================================================

-- Test 1: Check table availability for a specific date/time
-- This query is used in Reservation::checkTableAvailability()
SELECT COUNT(*) as count 
FROM reservations r
JOIN reservation_tables rt ON r.id = rt.reservation_id
WHERE rt.table_id IN (1, 2, 3) -- Example table IDs
AND r.status IN ('pendiente', 'confirmada') 
AND ABS(TIMESTAMPDIFF(MINUTE, r.reservation_datetime, '2024-12-26 14:00:00')) < 120; -- 2 hour buffer

-- Expected result: 0 means tables are available, >0 means tables are occupied

-- Test 2: Get available tables for reservation edit (excluding current reservation)
-- This query is used in Table::getAvailableTablesForReservationEdit()
SELECT DISTINCT t.* FROM tables t 
WHERE t.active = 1 
AND (t.status = 'disponible' OR t.id IN (
    SELECT rt.table_id FROM reservation_tables rt 
    WHERE rt.reservation_id = 1 -- Example reservation ID
))
ORDER BY t.number ASC;

-- Expected result: All available tables plus tables already assigned to the reservation

-- Test 3: Get all reservations with table details
-- This query is used in Reservation::getReservationsWithTables()
SELECT r.*, 
       c.name as customer_name, 
       c.phone as customer_phone,
       u.name as waiter_name,
       GROUP_CONCAT(DISTINCT CONCAT('Mesa ', t.number, ' (', t.capacity, ')') ORDER BY t.number ASC SEPARATOR ', ') as table_details,
       SUM(t.capacity) as total_capacity
FROM reservations r 
LEFT JOIN customers c ON r.customer_id = c.id
LEFT JOIN reservation_tables rt ON r.id = rt.reservation_id
LEFT JOIN tables t ON rt.table_id = t.id
LEFT JOIN waiters w ON r.waiter_id = w.id
LEFT JOIN users u ON w.user_id = u.id
WHERE r.reservation_datetime > NOW() 
GROUP BY r.id 
ORDER BY r.reservation_datetime ASC;

-- Expected result: List of future reservations with customer and table information

-- ============================================================================
-- ORDER EDITING AND ITEM MANAGEMENT OPERATIONS
-- ============================================================================

-- Test 4: Add new item to order
-- This operation is performed in Order::addItemToOrder()
-- Example: Add item to order
INSERT INTO order_items (order_id, dish_id, quantity, unit_price, subtotal, notes, created_at)
VALUES (1, 5, 2, 15.50, 31.00, 'Sin cebolla', NOW());

-- Expected result: New item added to order

-- Test 5: Update order total after adding items
-- This query is used in Order::updateOrderTotal()
UPDATE orders 
SET total = (
    SELECT COALESCE(SUM(subtotal), 0) 
    FROM order_items 
    WHERE order_id = 1 -- Example order ID
) 
WHERE id = 1;

-- Expected result: Order total updated to sum of all item subtotals

-- Test 6: Remove item from order
-- This operation is performed in Order::removeItemFromOrder()
DELETE FROM order_items WHERE id = 1; -- Example item ID

-- Then update the order total
UPDATE orders 
SET total = (
    SELECT COALESCE(SUM(subtotal), 0) 
    FROM order_items 
    WHERE order_id = 1 -- Order ID of the removed item
) 
WHERE id = 1;

-- Expected result: Item removed and order total recalculated

-- Test 7: Update table status when order is assigned
-- This operation is used in OrdersController::processEdit()
UPDATE tables SET status = 'ocupada' WHERE id = 3; -- Example table ID

-- Expected result: Table marked as occupied

-- Test 8: Check for other active orders on a table before freeing it
-- This query is used in OrdersController::processEdit()
SELECT COUNT(*) as active_orders
FROM orders 
WHERE table_id = 3 -- Example table ID
AND status IN ('pendiente', 'en_preparacion', 'listo');

-- If count is 0 or 1, table can be freed:
UPDATE tables SET status = 'disponible' WHERE id = 3;

-- Expected result: Table freed only if no other active orders

-- ============================================================================
-- DATA INTEGRITY AND VALIDATION QUERIES
-- ============================================================================

-- Test 9: Verify reservation-table relationship integrity
SELECT r.id as reservation_id, 
       r.reservation_datetime,
       COUNT(rt.table_id) as table_count,
       GROUP_CONCAT(t.number ORDER BY t.number) as table_numbers
FROM reservations r
LEFT JOIN reservation_tables rt ON r.id = rt.reservation_id
LEFT JOIN tables t ON rt.table_id = t.id
GROUP BY r.id
HAVING table_count > 0
ORDER BY r.reservation_datetime;

-- Expected result: All reservations with their assigned tables

-- Test 10: Verify order totals match item subtotals
SELECT o.id as order_id,
       o.total as order_total,
       COALESCE(SUM(oi.subtotal), 0) as calculated_total,
       CASE 
           WHEN o.total = COALESCE(SUM(oi.subtotal), 0) THEN 'CORRECT'
           ELSE 'MISMATCH'
       END as status
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.total
ORDER BY o.id;

-- Expected result: All orders should show 'CORRECT' status

-- Test 11: Check table availability conflicts
SELECT t.id as table_id,
       t.number,
       t.status,
       COUNT(DISTINCT r.id) as reservation_count,
       COUNT(DISTINCT o.id) as order_count
FROM tables t
LEFT JOIN reservation_tables rt ON t.id = rt.table_id
LEFT JOIN reservations r ON rt.reservation_id = r.id 
    AND r.status IN ('pendiente', 'confirmada')
    AND r.reservation_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 DAY)
LEFT JOIN orders o ON t.id = o.table_id 
    AND o.status IN ('pendiente', 'en_preparacion', 'listo')
WHERE t.active = 1
GROUP BY t.id, t.number, t.status
ORDER BY t.number;

-- Expected result: Shows table usage to identify potential conflicts

-- ============================================================================
-- PERFORMANCE AND MONITORING QUERIES
-- ============================================================================

-- Test 12: Monitor table utilization
SELECT 
    status,
    COUNT(*) as count,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tables WHERE active = 1)), 2) as percentage
FROM tables 
WHERE active = 1 
GROUP BY status;

-- Expected result: Table status distribution

-- Test 13: Monitor order processing times
SELECT 
    status,
    COUNT(*) as order_count,
    AVG(total) as avg_total,
    MIN(created_at) as oldest_order,
    MAX(created_at) as newest_order
FROM orders 
WHERE DATE(created_at) = CURDATE()
GROUP BY status
ORDER BY 
    CASE status
        WHEN 'pendiente_confirmacion' THEN 1
        WHEN 'pendiente' THEN 2
        WHEN 'en_preparacion' THEN 3
        WHEN 'listo' THEN 4
        WHEN 'entregado' THEN 5
        ELSE 6
    END;

-- Expected result: Daily order processing statistics

-- ============================================================================
-- CLEANUP AND MAINTENANCE QUERIES
-- ============================================================================

-- Test 14: Clean up old completed reservations (older than 30 days)
-- NOTE: Run this carefully in production!
-- SELECT COUNT(*) FROM reservations 
-- WHERE status = 'completada' 
-- AND reservation_datetime < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Test 15: Identify orphaned records
SELECT 'Orphaned order items' as issue, COUNT(*) as count
FROM order_items oi
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.id IS NULL

UNION ALL

SELECT 'Orphaned reservation tables' as issue, COUNT(*) as count
FROM reservation_tables rt
LEFT JOIN reservations r ON rt.reservation_id = r.id
WHERE r.id IS NULL

UNION ALL

SELECT 'Orders without items' as issue, COUNT(*) as count
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
WHERE oi.order_id IS NULL AND o.status != 'pendiente_confirmacion';

-- Expected result: All counts should be 0 for data integrity

-- ============================================================================
-- NOTES
-- ============================================================================

/*
All SQL operations in this file are designed for MySQL and avoid SQLite-specific syntax.
Key features:
1. Uses standard SQL syntax compatible with MySQL 5.7+
2. Implements proper foreign key relationships
3. Uses transactions for data consistency
4. Includes proper indexing strategies
5. Maintains referential integrity

Performance considerations:
- reservation_tables junction table allows efficient many-to-many relationships
- Proper indexes on datetime fields for quick availability checks
- Aggregate functions used efficiently for reporting
- Status-based filtering reduces query complexity

Security considerations:
- All queries use parameterized statements in the PHP code
- Input validation performed before database operations
- Proper escaping of user input
- Transaction rollback on errors
*/