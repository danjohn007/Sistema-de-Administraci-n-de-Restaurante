<?php
class Order extends BaseModel {
    protected $table = 'orders';
    
    public function getOrdersWithDetails($filters = []) {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         COUNT(oi.id) as items_count
                  FROM {$this->table} o
                  JOIN tables t ON o.table_id = t.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u ON w.user_id = u.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id";
        
        $conditions = [];
        $params = [];
        
        if (isset($filters['status'])) {
            $conditions[] = "o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['waiter_id'])) {
            $conditions[] = "o.waiter_id = ?";
            $params[] = $filters['waiter_id'];
        }
        
        if (isset($filters['table_id'])) {
            $conditions[] = "o.table_id = ?";
            $params[] = $filters['table_id'];
        }
        
        if (isset($filters['date'])) {
            $conditions[] = "DATE(o.created_at) = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, d.name as dish_name, d.category
                  FROM order_items oi
                  JOIN dishes d ON oi.dish_id = d.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$orderId]);
        
        return $stmt->fetchAll();
    }
    
    public function createOrderWithItems($orderData, $items) {
        try {
            $this->db->beginTransaction();
            
            // Create order
            $orderId = $this->create($orderData);
            if (!$orderId) {
                throw new Exception('Error al crear la orden');
            }
            
            // Add items
            $orderItemModel = new OrderItem();
            $total = 0;
            
            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $item['subtotal'] = $item['quantity'] * $item['unit_price'];
                $total += $item['subtotal'];
                
                if (!$orderItemModel->create($item)) {
                    throw new Exception('Error al agregar item a la orden');
                }
            }
            
            // Update order total
            $this->update($orderId, ['total' => $total]);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateOrderStatus($orderId, $status) {
        return $this->update($orderId, ['status' => $status]);
    }
    
    public function addItemToOrder($orderId, $dishId, $quantity, $unitPrice, $notes = null) {
        try {
            $this->db->beginTransaction();
            
            // Add item
            $orderItemModel = new OrderItem();
            $subtotal = $quantity * $unitPrice;
            
            $itemData = [
                'order_id' => $orderId,
                'dish_id' => $dishId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $notes
            ];
            
            if (!$orderItemModel->create($itemData)) {
                throw new Exception('Error al agregar item');
            }
            
            // Update order total
            $this->updateOrderTotal($orderId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function removeItemFromOrder($orderItemId) {
        try {
            $this->db->beginTransaction();
            
            // Get order ID first
            $orderItemModel = new OrderItem();
            $item = $orderItemModel->find($orderItemId);
            
            if (!$item) {
                throw new Exception('Item no encontrado');
            }
            
            // Delete item
            $orderItemModel->delete($orderItemId);
            
            // Update order total
            $this->updateOrderTotal($item['order_id']);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateOrderTotal($orderId) {
        $query = "UPDATE {$this->table} 
                  SET total = (
                      SELECT COALESCE(SUM(subtotal), 0) 
                      FROM order_items 
                      WHERE order_id = ?
                  ) 
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$orderId, $orderId]);
    }
    
    public function getOrdersByStatus($status) {
        return $this->findAll(['status' => $status], 'created_at ASC');
    }
    
    public function getDailySales($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total), 0) as total_sales,
                    AVG(total) as average_order
                  FROM {$this->table}
                  WHERE DATE(created_at) = ? AND status = 'entregado'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$date]);
        
        return $stmt->fetch();
    }
    
    public function getOrdersReadyForTicket() {
        // Get orders that are ready to be converted to tickets (status = 'entregado')
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code
                  FROM {$this->table} o
                  JOIN tables t ON o.table_id = t.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u ON w.user_id = u.id
                  WHERE o.status = 'entregado'
                  ORDER BY o.updated_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>