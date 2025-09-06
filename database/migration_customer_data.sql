-- Migration script to populate customer data for the Best Diners module
-- This script creates sample customer records if the customers table is empty

-- Insert sample customers if table is empty
INSERT INTO customers (name, phone, email, birthday, total_visits, total_spent, created_at) 
SELECT * FROM (
    SELECT 'María González' as name, '555-0001' as phone, 'maria@email.com' as email, '15/03' as birthday, 25 as total_visits, 2850.75 as total_spent, DATE_SUB(NOW(), INTERVAL 6 MONTH) as created_at
    UNION ALL
    SELECT 'Juan Pérez', '555-0002', 'juan@email.com', '22/07', 22, 2456.50, DATE_SUB(NOW(), INTERVAL 5 MONTH)
    UNION ALL
    SELECT 'Ana Rodríguez', '555-0003', 'ana@email.com', '08/12', 28, 3120.25, DATE_SUB(NOW(), INTERVAL 8 MONTH)
    UNION ALL
    SELECT 'Carlos Martínez', '555-0004', 'carlos@email.com', '14/09', 18, 1875.00, DATE_SUB(NOW(), INTERVAL 4 MONTH)
    UNION ALL
    SELECT 'Laura Sánchez', '555-0005', 'laura@email.com', '03/05', 32, 3650.80, DATE_SUB(NOW(), INTERVAL 10 MONTH)
    UNION ALL
    SELECT 'Roberto García', '555-0006', 'roberto@email.com', '27/11', 15, 1420.25, DATE_SUB(NOW(), INTERVAL 3 MONTH)
    UNION ALL
    SELECT 'Carmen López', '555-0007', 'carmen@email.com', '18/06', 24, 2680.50, DATE_SUB(NOW(), INTERVAL 7 MONTH)
    UNION ALL
    SELECT 'Miguel Torres', '555-0008', 'miguel@email.com', '05/02', 20, 2150.75, DATE_SUB(NOW(), INTERVAL 6 MONTH)
    UNION ALL
    SELECT 'Isabel Morales', '555-0009', 'isabel@email.com', '12/10', 26, 2945.30, DATE_SUB(NOW(), INTERVAL 9 MONTH)
    UNION ALL
    SELECT 'Francisco Ruiz', '555-0010', 'francisco@email.com', '29/04', 19, 1985.60, DATE_SUB(NOW(), INTERVAL 5 MONTH)
    UNION ALL
    SELECT 'Elena Jiménez', '555-0011', 'elena@email.com', '07/08', 30, 3285.45, DATE_SUB(NOW(), INTERVAL 11 MONTH)
    UNION ALL
    SELECT 'Pedro Herrera', '555-0012', 'pedro@email.com', '21/01', 16, 1650.90, DATE_SUB(NOW(), INTERVAL 4 MONTH)
    UNION ALL
    SELECT 'Rosa Vargas', '555-0013', 'rosa@email.com', '16/12', 23, 2495.85, DATE_SUB(NOW(), INTERVAL 8 MONTH)
    UNION ALL
    SELECT 'Antonio Medina', '555-0014', 'antonio@email.com', '09/06', 21, 2280.40, DATE_SUB(NOW(), INTERVAL 7 MONTH)
    UNION ALL
    SELECT 'Lucía Castillo', '555-0015', 'lucia@email.com', '04/09', 27, 2875.70, DATE_SUB(NOW(), INTERVAL 10 MONTH)
) tmp
WHERE NOT EXISTS (SELECT 1 FROM customers LIMIT 1);

-- Update the auto_increment value to continue from the right number
ALTER TABLE customers AUTO_INCREMENT = 16;