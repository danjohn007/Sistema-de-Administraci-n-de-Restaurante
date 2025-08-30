-- Migration to make table_id optional in orders and reservations
-- This is a non-destructive change that allows null values

USE ejercito_restaurant;

-- Make table_id nullable in orders table to support orders without assigned tables
ALTER TABLE orders 
MODIFY COLUMN table_id INT NULL;

-- Make table_id nullable in reservations table to support reservations without specific table preference
ALTER TABLE reservations 
MODIFY COLUMN table_id INT NULL;

-- Update foreign key constraints to handle null values properly
-- The CASCADE behavior is maintained for non-null values