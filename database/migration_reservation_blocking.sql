-- Migration script for table reservation blocking functionality
-- This script adds table unblock logging for reservation system

USE ejercito_restaurant;

-- Create table unblock log for tracking admin/cashier actions
CREATE TABLE IF NOT EXISTS table_unblock_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_id INT NOT NULL,
    unblocked_by INT NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (unblocked_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Add index for better performance
CREATE INDEX idx_table_unblock_log_table ON table_unblock_log(table_id);
CREATE INDEX idx_table_unblock_log_date ON table_unblock_log(created_at);