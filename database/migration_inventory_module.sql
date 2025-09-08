-- =======================================================
-- MÓDULO DE INVENTARIOS - SISTEMA DE ADMINISTRACIÓN DE RESTAURANTE
-- Migración para agregar las tablas del módulo de inventarios
-- =======================================================

USE ejercito_restaurant;

-- Tabla de productos de inventario
CREATE TABLE IF NOT EXISTS inventory_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    unit_measure VARCHAR(50) DEFAULT 'unidad', -- kg, litros, unidades, etc.
    current_stock DECIMAL(10, 3) DEFAULT 0.000,
    min_stock DECIMAL(10, 3) DEFAULT 0.000, -- Stock mínimo para alertas
    max_stock DECIMAL(10, 3) DEFAULT 0.000, -- Stock máximo
    cost_per_unit DECIMAL(10, 2) DEFAULT 0.00, -- Costo promedio por unidad
    is_dish_ingredient BOOLEAN DEFAULT FALSE, -- Si es ingrediente de platillos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE
);

-- Tabla de movimientos de inventario
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    movement_type ENUM('entrada', 'salida') NOT NULL, -- entrada = compra, salida = venta
    quantity DECIMAL(10, 3) NOT NULL,
    cost_per_unit DECIMAL(10, 2) DEFAULT 0.00,
    total_cost DECIMAL(10, 2) DEFAULT 0.00,
    reference_type ENUM('expense', 'ticket', 'adjustment', 'manual') NOT NULL,
    reference_id INT NULL, -- ID del gasto, ticket, etc.
    description TEXT,
    user_id INT NOT NULL, -- Usuario que registra el movimiento
    movement_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES inventory_products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de recetas (relación entre platillos e ingredientes)
CREATE TABLE IF NOT EXISTS dish_ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dish_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_needed DECIMAL(10, 3) NOT NULL, -- Cantidad necesaria del ingrediente
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES inventory_products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dish_ingredient (dish_id, product_id)
);

-- Tabla de configuraciones del sistema
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Agregar la configuración para habilitar/deshabilitar cobranza
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('collections_enabled', '1', 'Habilitar o deshabilitar la funcionalidad de cuentas por cobrar (1 = habilitado, 0 = deshabilitado)'),
('inventory_enabled', '1', 'Habilitar o deshabilitar el módulo de inventarios (1 = habilitado, 0 = deshabilitado)'),
('auto_deduct_inventory', '1', 'Descontar automáticamente del inventario al generar tickets (1 = habilitado, 0 = deshabilitado)');

-- Agregar rol de superadministrador si no existe
ALTER TABLE users MODIFY COLUMN role ENUM('administrador', 'mesero', 'cajero', 'superadmin') NOT NULL;

-- Índices para mejor rendimiento
CREATE INDEX idx_inventory_products_category ON inventory_products(category);
CREATE INDEX idx_inventory_products_active ON inventory_products(active);
CREATE INDEX idx_inventory_movements_product ON inventory_movements(product_id);
CREATE INDEX idx_inventory_movements_type ON inventory_movements(movement_type);
CREATE INDEX idx_inventory_movements_reference ON inventory_movements(reference_type, reference_id);
CREATE INDEX idx_inventory_movements_date ON inventory_movements(movement_date);
CREATE INDEX idx_dish_ingredients_dish ON dish_ingredients(dish_id);
CREATE INDEX idx_dish_ingredients_product ON dish_ingredients(product_id);

-- Datos iniciales para categorías de productos
INSERT INTO inventory_products (name, description, category, unit_measure, min_stock, max_stock, cost_per_unit, is_dish_ingredient) VALUES 
('Pollo (kg)', 'Pollo fresco por kilogramo', 'Carnes', 'kg', 5.000, 50.000, 85.00, TRUE),
('Res (kg)', 'Carne de res por kilogramo', 'Carnes', 'kg', 3.000, 30.000, 120.00, TRUE),
('Arroz (kg)', 'Arroz blanco por kilogramo', 'Granos', 'kg', 10.000, 100.000, 25.00, TRUE),
('Frijol (kg)', 'Frijol negro por kilogramo', 'Granos', 'kg', 5.000, 50.000, 30.00, TRUE),
('Aceite (litros)', 'Aceite vegetal para cocinar', 'Aceites', 'litros', 2.000, 20.000, 35.00, TRUE),
('Sal (kg)', 'Sal de mesa por kilogramo', 'Condimentos', 'kg', 1.000, 10.000, 15.00, TRUE),
('Cebolla (kg)', 'Cebolla blanca por kilogramo', 'Verduras', 'kg', 5.000, 25.000, 20.00, TRUE),
('Tomate (kg)', 'Tomate rojo por kilogramo', 'Verduras', 'kg', 3.000, 15.000, 25.00, TRUE),
('Refresco Coca-Cola (unidades)', 'Refresco Coca-Cola 600ml', 'Bebidas', 'unidades', 24.000, 120.000, 18.00, FALSE),
('Agua embotellada (unidades)', 'Agua purificada 500ml', 'Bebidas', 'unidades', 24.000, 120.000, 8.00, FALSE);

COMMIT;