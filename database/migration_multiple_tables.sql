-- Migration to enhance reservation system for multiple tables and waiter assignment
-- This script adds support for multiple table selection and waiter assignment

USE ejercito_restaurant;

-- Make table_id nullable in reservations table to support multi-table reservations
ALTER TABLE reservations 
MODIFY COLUMN table_id INT NULL;

-- Create reservation_tables junction table for multiple table support
CREATE TABLE IF NOT EXISTS reservation_tables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL,
    table_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation_table (reservation_id, table_id)
);

-- Add waiter assignment to reservations
ALTER TABLE reservations 
ADD COLUMN waiter_id INT NULL AFTER table_id,
ADD FOREIGN KEY (waiter_id) REFERENCES waiters(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX idx_reservation_tables_reservation ON reservation_tables(reservation_id);
CREATE INDEX idx_reservation_tables_table ON reservation_tables(table_id);
CREATE INDEX idx_reservations_waiter ON reservations(waiter_id);

-- Migrate existing single table reservations to the new system
INSERT INTO reservation_tables (reservation_id, table_id)
SELECT id, table_id 
FROM reservations 
WHERE table_id IS NOT NULL;