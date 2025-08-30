<?php
// Application configuration
define('APP_NAME', 'Sistema de Administración de Restaurante');
define('APP_VERSION', '1.0.0');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'exhacien_restaurante');
define('DB_USER', 'exhacien_restaurante');
define('DB_PASS', 'Danjohn007!');
define('DB_CHARSET', 'utf8mb4');

// Security
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Application settings
define('ITEMS_PER_PAGE', 10);
define('UPLOAD_PATH', BASE_PATH . '/public/images/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// User roles
define('ROLE_ADMIN', 'administrador');
define('ROLE_WAITER', 'mesero');
define('ROLE_CASHIER', 'cajero');

// Financial module constants
define('UPLOAD_EVIDENCE_PATH', BASE_PATH . '/public/uploads/evidence/');
define('ALLOWED_EVIDENCE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Branch staff roles
define('BRANCH_ROLE_MANAGER', 'gerente');
define('BRANCH_ROLE_CASHIER', 'cajero');
define('BRANCH_ROLE_WAITER', 'mesero');
define('BRANCH_ROLE_COOK', 'cocinero');

// Table statuses
define('TABLE_AVAILABLE', 'disponible');
define('TABLE_OCCUPIED', 'ocupada');
define('TABLE_BILL_REQUESTED', 'cuenta_solicitada');
define('TABLE_CLOSED', 'cerrada');

// Order statuses
define('ORDER_PENDING_CONFIRMATION', 'pendiente_confirmacion');
define('ORDER_PENDING', 'pendiente');
define('ORDER_PREPARING', 'en_preparacion');
define('ORDER_READY', 'listo');
define('ORDER_DELIVERED', 'entregado');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Mexico_City');
?>
