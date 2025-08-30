# Manual Testing Guide

## Setup
1. Run the database migration script: `database/migration_public_orders.sql`
2. Ensure the web server is configured with the correct BASE_URL
3. Make sure sample data exists (users, waiters, dishes, tables)

## Test Cases

### 1. Main Issue Fix: Cashier Order Creation
**Test**: Cashier creates an order
**Steps**:
1. Login as cashier user
2. Navigate to `/orders/create`
3. Verify waiter dropdown is visible and required
4. Select table, waiter, and dishes
5. Submit order
**Expected**: Order created successfully without waiter_id errors

### 2. Waiter Read-Only Access
**Test**: Waiter views menu and tables
**Steps**:
1. Login as waiter user
2. Navigate to `/dishes`
3. Navigate to `/tables`
**Expected**: 
- Pages show "Consultar" instead of "Gestión"
- No create/edit/delete buttons visible
- View/read functionality works

### 3. Public Menu Access
**Test**: Public user places order
**Steps**:
1. Navigate to `/public/menu` (no login required)
2. Fill customer information
3. Select table and dishes
4. Test both regular and pickup orders
5. Submit order
**Expected**: 
- Order created with "Pendiente Confirmación" status
- Success page displays order number
- No table status change for pickup orders

### 4. Public Order Confirmation
**Test**: Admin/Cashier confirms public order
**Steps**:
1. Login as admin or cashier
2. Navigate to `/orders`
3. Find order with "Pendiente Confirmación" status
4. Click confirm button
5. Assign waiter and confirm
**Expected**: 
- Order status changes to "Pendiente"
- Waiter assigned successfully
- Order appears in normal workflow

### 5. Order Status Display
**Test**: Verify status display
**Steps**:
1. Check orders index page
2. Verify all status badges display correctly
3. Check statistics cards
**Expected**:
- New "Sin Confirmar" statistics card appears for admin/cashier
- Public orders show customer info instead of waiter
- Pickup badge displays for pickup orders

## URLs to Test
- `/orders/create` (cashier login)
- `/dishes` (waiter login)
- `/tables` (waiter login)
- `/public/menu` (no login)
- `/orders` (admin/cashier login)
- `/orders/confirmPublicOrder/{id}` (admin/cashier login)

## Database Verification
After testing, verify database records:
```sql
-- Check orders with public fields
SELECT id, customer_name, customer_phone, is_pickup, status FROM orders WHERE customer_name IS NOT NULL;

-- Check order statuses
SELECT status, COUNT(*) FROM orders GROUP BY status;
```

## Expected Behavior Summary
1. Cashiers can create orders (main bug fixed)
2. Waiters have read-only access to menu and tables
3. Public users can place orders without authentication
4. Public orders require admin/cashier confirmation
5. Pickup orders don't reserve tables
6. All existing functionality remains unchanged