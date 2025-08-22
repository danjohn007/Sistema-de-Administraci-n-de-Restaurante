-- Datos de ejemplo para el sistema de restaurante
USE restaurante_db;

-- Insertar usuarios (contraseñas: todas son "123456")
INSERT INTO users (email, password, name, role) VALUES
('admin@restaurante.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'administrador'),
('cajero@restaurante.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 'cajero'),
('mesero1@restaurante.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'mesero'),
('mesero2@restaurante.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana López', 'mesero');

-- Insertar meseros
INSERT INTO waiters (user_id, employee_code, phone) VALUES
(3, 'MES001', '555-1234'),
(4, 'MES002', '555-5678');

-- Insertar mesas
INSERT INTO tables (number, capacity, status) VALUES
(1, 2, 'disponible'),
(2, 4, 'disponible'),
(3, 6, 'disponible'),
(4, 4, 'disponible'),
(5, 2, 'disponible'),
(6, 8, 'disponible'),
(7, 4, 'disponible'),
(8, 2, 'disponible');

-- Insertar platillos del menú
INSERT INTO dishes (name, description, price, category) VALUES
-- Entradas
('Guacamole con Totopos', 'Aguacate fresco con totopos caseros', 85.00, 'Entradas'),
('Nachos Supremos', 'Totopos con queso, jalapeños y crema', 120.00, 'Entradas'),
('Alitas Buffalo', '8 piezas de alitas con salsa buffalo', 145.00, 'Entradas'),
('Quesadillas de Flor de Calabaza', 'Tortillas de maíz con queso y flor de calabaza', 95.00, 'Entradas'),

-- Platos Principales
('Tacos al Pastor', '3 tacos con carne al pastor, piña y cebolla', 75.00, 'Platos Principales'),
('Enchiladas Verdes', '3 enchiladas con pollo, salsa verde y crema', 110.00, 'Platos Principales'),
('Carne Asada', 'Arrachera a la parrilla con guarniciones', 185.00, 'Platos Principales'),
('Pescado a la Veracruzana', 'Filete de pescado con salsa veracruzana', 165.00, 'Platos Principales'),
('Mole Poblano', 'Pollo con mole poblano tradicional', 155.00, 'Platos Principales'),
('Chiles en Nogada', 'Chile poblano relleno con nogada', 175.00, 'Platos Principales'),

-- Bebidas
('Agua de Jamaica', 'Agua fresca de jamaica natural', 25.00, 'Bebidas'),
('Agua de Horchata', 'Agua fresca de horchata con canela', 25.00, 'Bebidas'),
('Coca Cola', 'Refresco de cola 355ml', 30.00, 'Bebidas'),
('Cerveza Corona', 'Cerveza mexicana 355ml', 45.00, 'Bebidas'),
('Margarita Clásica', 'Tequila, triple sec y limón', 85.00, 'Bebidas'),

-- Postres
('Flan Napolitano', 'Flan casero con caramelo', 45.00, 'Postres'),
('Churros con Cajeta', 'Churros caseros con cajeta', 55.00, 'Postres'),
('Pastel Tres Leches', 'Pastel empapado en tres leches', 65.00, 'Postres');

-- Crear un pedido de ejemplo
INSERT INTO orders (table_id, waiter_id, status, notes) VALUES
(2, 1, 'pendiente', 'Cliente solicita sin chile');

-- Items del pedido de ejemplo
INSERT INTO order_items (order_id, dish_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 85.00, 85.00),
(1, 5, 2, 75.00, 150.00),
(1, 11, 2, 25.00, 50.00);

-- Actualizar total del pedido
UPDATE orders SET total = 285.00 WHERE id = 1;

-- Actualizar estado de la mesa
UPDATE tables SET status = 'ocupada', waiter_id = 1 WHERE id = 2;