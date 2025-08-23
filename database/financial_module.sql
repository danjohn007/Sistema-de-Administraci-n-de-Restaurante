-- =======================================================
-- MÓDULO FINANCIERO - SISTEMA DE ADMINISTRACIÓN DE RESTAURANTE
-- Sentencias SQL para agregar las tablas del módulo financiero
-- =======================================================

USE ejercito_restaurant;

-- Tabla de sucursales
CREATE TABLE IF NOT EXISTS branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    manager_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla de categorías de gastos
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff', -- Color hex para la UI
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE
);

-- Tabla de gastos
CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    branch_id INT,
    user_id INT NOT NULL, -- Usuario que registra el gasto
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    receipt_file VARCHAR(255), -- Archivo de evidencia
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de retiros de dinero
CREATE TABLE IF NOT EXISTS cash_withdrawals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT,
    responsible_user_id INT NOT NULL, -- Usuario responsable del retiro
    amount DECIMAL(10, 2) NOT NULL,
    reason TEXT NOT NULL,
    evidence_file VARCHAR(255), -- Archivo de evidencia
    authorized_by_user_id INT, -- Usuario que autoriza (admin)
    withdrawal_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (authorized_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla de cortes de caja
CREATE TABLE IF NOT EXISTS cash_closures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT,
    cashier_user_id INT NOT NULL, -- Cajero que realiza el corte
    shift_start DATETIME NOT NULL,
    shift_end DATETIME NOT NULL,
    initial_cash DECIMAL(10, 2) DEFAULT 0.00,
    final_cash DECIMAL(10, 2) DEFAULT 0.00,
    total_sales DECIMAL(10, 2) DEFAULT 0.00, -- Total de ventas en el período
    total_expenses DECIMAL(10, 2) DEFAULT 0.00, -- Total de gastos en el período
    total_withdrawals DECIMAL(10, 2) DEFAULT 0.00, -- Total de retiros en el período
    net_profit DECIMAL(10, 2) DEFAULT 0.00, -- Utilidad neta calculada
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (cashier_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de asignación de personal a sucursales (relación muchos a muchos)
CREATE TABLE IF NOT EXISTS branch_staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('gerente', 'cajero', 'mesero', 'cocinero') NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_branch_user (branch_id, user_id)
);

-- Índices para mejor rendimiento
CREATE INDEX idx_expenses_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category_id);
CREATE INDEX idx_expenses_branch ON expenses(branch_id);
CREATE INDEX idx_withdrawals_date ON cash_withdrawals(withdrawal_date);
CREATE INDEX idx_withdrawals_branch ON cash_withdrawals(branch_id);
CREATE INDEX idx_closures_date ON cash_closures(shift_start, shift_end);
CREATE INDEX idx_closures_branch ON cash_closures(branch_id);
CREATE INDEX idx_branch_staff_branch ON branch_staff(branch_id);
CREATE INDEX idx_branch_staff_user ON branch_staff(user_id);

-- Datos iniciales para el módulo financiero

-- Sucursal principal (por defecto)
INSERT INTO branches (name, address, phone, active) VALUES 
('Sucursal Principal', 'Dirección de la sucursal principal', '555-0000', 1);

-- Categorías de gastos predeterminadas
INSERT INTO expense_categories (name, description, color) VALUES 
('Suministros', 'Gastos en ingredientes y suministros de cocina', '#28a745'),
('Servicios', 'Gastos en servicios públicos (luz, agua, gas)', '#ffc107'),
('Mantenimiento', 'Gastos en reparaciones y mantenimiento', '#dc3545'),
('Marketing', 'Gastos en publicidad y marketing', '#6f42c1'),
('Personal', 'Gastos relacionados con el personal', '#fd7e14'),
('Otros', 'Gastos varios no categorizados', '#6c757d');

-- Asignar usuarios existentes a la sucursal principal
INSERT INTO branch_staff (branch_id, user_id, role, active)
SELECT 1, id, 
    CASE 
        WHEN role = 'administrador' THEN 'gerente'
        WHEN role = 'cajero' THEN 'cajero' 
        WHEN role = 'mesero' THEN 'mesero'
        ELSE 'mesero'
    END as branch_role,
    active
FROM users 
WHERE active = 1;

COMMIT;