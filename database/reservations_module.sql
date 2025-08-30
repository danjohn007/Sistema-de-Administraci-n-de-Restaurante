-- Migration script for reservations module
-- This script adds reservation functionality to the restaurant system

USE ejercito_restaurant;

-- Create reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_birthday DATE NULL,
    reservation_datetime DATETIME NOT NULL,
    party_size INT NOT NULL DEFAULT 1,
    notes TEXT NULL,
    status ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
);

-- Create customers table to track customer statistics
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    birthday DATE NULL,
    total_visits INT DEFAULT 0,
    total_spent DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add customer_id to orders table to link orders to customers
ALTER TABLE orders 
ADD COLUMN customer_id INT NULL AFTER customer_phone,
ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX idx_reservations_datetime ON reservations(reservation_datetime);
CREATE INDEX idx_reservations_table ON reservations(table_id);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_visits ON customers(total_visits DESC);
CREATE INDEX idx_customers_spent ON customers(total_spent DESC);
CREATE INDEX idx_orders_customer ON orders(customer_id);