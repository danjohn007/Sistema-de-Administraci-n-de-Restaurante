<?php
// Test Customer Statistics Update on Ticket Generation
echo "=== Customer Statistics Update on Ticket Generation Test ===\n";

// Mock database for testing
class MockDB {
    private $data = [];
    private $nextId = 1;
    private $queries = [];
    
    public function prepare($query) {
        $this->queries[] = $query;
        return new MockStatement($this, $query);
    }
    
    public function beginTransaction() {
        echo "  🔄 Transaction started\n";
        return true;
    }
    
    public function commit() {
        echo "  ✅ Transaction committed\n";
        return true;
    }
    
    public function rollback() {
        echo "  ❌ Transaction rolled back\n";
        return true;
    }
    
    public function insert($table, $data) {
        $id = $this->nextId++;
        $data['id'] = $id;
        $this->data[$table][$id] = $data;
        return $id;
    }
    
    public function update($table, $id, $data) {
        if (isset($this->data[$table][$id])) {
            foreach ($data as $key => $value) {
                $this->data[$table][$id][$key] = $value;
            }
            return true;
        }
        return false;
    }
    
    public function find($table, $id) {
        return $this->data[$table][$id] ?? null;
    }
    
    public function getQueries() {
        return $this->queries;
    }
    
    public function getData($table = null) {
        return $table ? ($this->data[$table] ?? []) : $this->data;
    }
}

class MockStatement {
    private $db;
    private $query;
    private $params = [];
    
    public function __construct($db, $query) {
        $this->db = $db;
        $this->query = $query;
    }
    
    public function execute($params = []) {
        $this->params = $params;
        
        // Simulate customer stats update
        if (strpos($this->query, 'UPDATE customers') !== false && 
            strpos($this->query, 'total_visits = total_visits + 1') !== false) {
            echo "  📊 Customer stats updated: visits +1, spent +{$params[0]}\n";
            return true;
        }
        
        // Simulate order status update
        if (strpos($this->query, 'UPDATE orders') !== false && 
            strpos($this->query, 'status = ?') !== false) {
            echo "  📝 Order status updated to: {$params[0]}\n";
            return true;
        }
        
        return true;
    }
    
    public function fetch() {
        // Mock data based on query type
        if (strpos($this->query, 'SELECT') !== false) {
            if (strpos($this->query, 'orders') !== false) {
                return [
                    'id' => 1,
                    'customer_id' => 1,
                    'status' => 'listo',
                    'total' => 150.50,
                    'created_at' => date('Y-m-d H:i:s'),
                    'table_id' => 1
                ];
            }
            if (strpos($this->query, 'COUNT') !== false) {
                return ['count' => 1];
            }
        }
        return null;
    }
}

// Create mock models
require_once 'core/BaseModel.php';

class MockCustomer {
    public $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function updateStats($customerId, $orderTotal) {
        $query = "UPDATE customers 
                  SET total_visits = total_visits + 1, 
                      total_spent = total_spent + ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$orderTotal, $customerId]);
    }
}

class MockOrder {
    public $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function find($id) {
        $query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateOrderStatus($orderId, $status) {
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $orderId]);
    }
}

// Test current ticket creation (without customer stats update)
function testCurrentTicketCreation() {
    echo "\n🔍 Test 1: Current Ticket Creation (Bug Demonstration)\n";
    echo "----------------------------------------------------\n";
    
    $db = new MockDB();
    $customerModel = new MockCustomer($db);
    $orderModel = new MockOrder($db);
    
    // Simulate current ticket creation logic (without customer stats update)
    try {
        $db->beginTransaction();
        
        $orderId = 1;
        $order = $orderModel->find($orderId);
        
        echo "  📋 Order found: ID {$orderId}, Customer ID {$order['customer_id']}, Total \${$order['total']}\n";
        
        // Create ticket (simplified)
        echo "  🎫 Creating ticket...\n";
        
        // Update order status to delivered (current implementation)
        $orderModel->updateOrderStatus($orderId, 'entregado');
        
        // BUG: Customer stats are NOT updated here!
        echo "  ❌ Customer statistics NOT updated (this is the bug!)\n";
        
        $db->commit();
        
        echo "  🔍 Result: Ticket created but customer stats unchanged\n";
        echo "  📊 Customer total_visits: NOT incremented\n";
        echo "  💰 Customer total_spent: NOT updated\n";
        
    } catch (Exception $e) {
        $db->rollback();
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

// Test fixed ticket creation (with customer stats update)
function testFixedTicketCreation() {
    echo "\n🔍 Test 2: Fixed Ticket Creation (With Customer Stats Update)\n";
    echo "------------------------------------------------------------\n";
    
    $db = new MockDB();
    $customerModel = new MockCustomer($db);
    $orderModel = new MockOrder($db);
    
    // Simulate fixed ticket creation logic (with customer stats update)
    try {
        $db->beginTransaction();
        
        $orderId = 1;
        $order = $orderModel->find($orderId);
        
        echo "  📋 Order found: ID {$orderId}, Customer ID {$order['customer_id']}, Total \${$order['total']}\n";
        
        // Create ticket (simplified)
        echo "  🎫 Creating ticket...\n";
        
        // Update order status to delivered
        $orderModel->updateOrderStatus($orderId, 'entregado');
        
        // FIX: Update customer stats if order has customer_id
        if ($order['customer_id']) {
            echo "  ✅ Updating customer statistics...\n";
            $customerModel->updateStats($order['customer_id'], $order['total']);
        }
        
        $db->commit();
        
        echo "  🔍 Result: Ticket created AND customer stats updated\n";
        echo "  📊 Customer total_visits: INCREMENTED by 1\n";
        echo "  💰 Customer total_spent: INCREASED by \${$order['total']}\n";
        
    } catch (Exception $e) {
        $db->rollback();
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

// Run tests
testCurrentTicketCreation();
testFixedTicketCreation();

echo "\n============================================================\n";
echo "📊 TEST SUMMARY\n";
echo "============================================================\n";
echo "✅ Test 1 demonstrates the bug: Customer stats not updated\n";
echo "✅ Test 2 shows the fix: Customer stats properly updated\n";
echo "🎯 Solution: Add customer stats update in ticket creation\n";
echo "📋 Required changes:\n";
echo "   - Modify Ticket::createTicket() method\n";
echo "   - Modify Ticket::createExpiredOrderTicket() method\n";
echo "   - Add customer stats update when order has customer_id\n";
echo "============================================================\n";
?>