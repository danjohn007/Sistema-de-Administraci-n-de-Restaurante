-- Migration to add table zones functionality
-- Date: 2024-12-23
-- Description: Add zone field to tables and create table_zones management

USE ejercito_restaurant;

-- Add zone field to tables
ALTER TABLE tables 
ADD COLUMN zone VARCHAR(50) DEFAULT 'Salón' 
AFTER capacity;

-- Create table_zones table for managing available zones
CREATE TABLE IF NOT EXISTS table_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default zones
INSERT INTO table_zones (name, description, color) VALUES
('Salón', 'Área principal del restaurante', '#007bff'),
('Terraza', 'Área exterior con vista', '#28a745'),
('Alberca', 'Área junto a la alberca', '#17a2b8'),
('Spa', 'Zona tranquila cerca del spa', '#6f42c1'),
('Room Service', 'Servicio a habitaciones', '#fd7e14')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    color = VALUES(color);

-- Update existing tables to have default zone
UPDATE tables SET zone = 'Salón' WHERE zone IS NULL;