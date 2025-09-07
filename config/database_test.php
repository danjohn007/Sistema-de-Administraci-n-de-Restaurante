<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Use SQLite for testing
            $this->connection = new PDO('sqlite::memory:');
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Create basic tables for testing
            $this->createTestTables();
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    private function createTestTables() {
        // Create users table
        $this->connection->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert test admin user
        $stmt = $this->connection->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin Test', 'admin@test.com', password_hash('admin123', PASSWORD_DEFAULT), 'administrador']);
        
        // Create customers table
        $this->connection->exec("
            CREATE TABLE customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT,
                email TEXT,
                total_spent DECIMAL(10,2) DEFAULT 0,
                total_visits INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert sample customers
        $customers = [
            ['Juan Pérez', '555-1234', 'juan@email.com', 1250.00, 15],
            ['María García', '555-5678', 'maria@email.com', 980.50, 12],
            ['Carlos López', '555-9012', 'carlos@email.com', 750.25, 8]
        ];
        
        $stmt = $this->connection->prepare("INSERT INTO customers (name, phone, email, total_spent, total_visits) VALUES (?, ?, ?, ?, ?)");
        foreach ($customers as $customer) {
            $stmt->execute($customer);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($query) {
        return $this->connection->prepare($query);
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Global function to get database instance
function db() {
    return Database::getInstance();
}
?>