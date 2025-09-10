-- Migration script for ticket cancellation functionality
-- This script adds cancellation functionality to tickets

USE ejercito_restaurant;

-- Add cancellation fields to tickets table
ALTER TABLE tickets 
ADD COLUMN status ENUM('active', 'cancelled') DEFAULT 'active' AFTER payment_method,
ADD COLUMN cancelled_at TIMESTAMP NULL AFTER status,
ADD COLUMN cancelled_by INT NULL AFTER cancelled_at,
ADD COLUMN cancellation_reason TEXT NULL AFTER cancelled_by,
ADD FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for ticket status
CREATE INDEX idx_tickets_status ON tickets(status);