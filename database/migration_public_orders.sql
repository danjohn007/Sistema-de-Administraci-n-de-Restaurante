-- Migration script to add public order functionality
-- This script adds new columns to support public orders without affecting existing data

USE ejercito_restaurant;

-- Add new columns to orders table for public orders
ALTER TABLE orders 
ADD COLUMN customer_name VARCHAR(255) NULL AFTER notes,
ADD COLUMN customer_phone VARCHAR(20) NULL AFTER customer_name,
ADD COLUMN is_pickup BOOLEAN DEFAULT FALSE AFTER customer_phone,
ADD COLUMN pickup_datetime DATETIME NULL AFTER is_pickup;

-- Update the status enum to include the new pending_confirmation status
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pendiente_confirmacion', 'pendiente', 'en_preparacion', 'listo', 'entregado') DEFAULT 'pendiente';

-- Allow waiter_id to be NULL for public orders that haven't been confirmed yet
ALTER TABLE orders 
MODIFY COLUMN waiter_id INT NULL;

-- Add indexes for better performance
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_is_pickup ON orders(is_pickup);
CREATE INDEX idx_orders_customer_phone ON orders(customer_phone);