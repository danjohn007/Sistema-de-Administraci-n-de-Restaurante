-- Migration to add missing payment methods to tickets table
-- This fixes the ENUM constraint to allow 'intercambio' and 'pendiente_por_cobrar' payment methods

USE ejercito_restaurant;

-- Alter tickets table to include new payment methods
ALTER TABLE tickets 
MODIFY COLUMN payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar') DEFAULT 'efectivo';

-- Commit the changes
COMMIT;