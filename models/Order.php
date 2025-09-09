<?php
class Order extends BaseModel {
    protected $table = 'orders';
    
    public function getOrdersWithDetails($filters = []) {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         COUNT(oi.id) as items_count,
                         COALESCE(SUM(oi.subtotal), 0) as total
                  FROM {$this->table} o
                  LEFT JOIN tables t ON o.table_id = t.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
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
        
        if (isset($filters['id'])) {
            $conditions[] = "o.id = ?";
            $params[] = $filters['id'];
        }
        
        if (isset($filters['date'])) {
            $conditions[] = "DATE(o.created_at) = ?";
            $params[] = $filters['date'];
        }
        
        if (isset($filters['pickup_date_from'])) {
            $conditions[] = "DATE(o.pickup_datetime) >= ?";
            $params[] = $filters['pickup_date_from'];
        }
        
        if (isset($filters['pickup_date_to'])) {
            $conditions[] = "DATE(o.pickup_datetime) <= ?";
            $params[] = $filters['pickup_date_to'];
        }
        
        if (isset($filters['is_pickup'])) {
            $conditions[] = "o.is_pickup = ?";
            $params[] = $filters['is_pickup'];
        }
        
        if (isset($filters['future_orders']) && $filters['future_orders']) {
            $conditions[] = "o.is_pickup = 1 AND DATE(o.pickup_datetime) > CURDATE()";
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
            
            // Validate dish availability before creating order
            $dishModel = new Dish();
            $today = date('Y-m-d');
            $currentDay = date('N');
            
            foreach ($items as $item) {
                if (!$dishModel->isDishAvailable($item['dish_id'], $today, $currentDay)) {
                    $dish = $dishModel->find($item['dish_id']);
                    $validityStatus = $dishModel->getValidityStatus($dish);
                    throw new Exception('El platillo "' . $dish['name'] . '" no está disponible: ' . $validityStatus['message']);
                }
            }
            
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
    
    public function createPublicOrderWithItems($orderData, $items) {
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
    
    // Helper method for creating orders with items within an existing transaction
    private function createOrderWithItemsInTransaction($orderData, $items) {
        // Validate dish availability before creating order
        $dishModel = new Dish();
        $today = date('Y-m-d');
        $currentDay = date('N');
        
        foreach ($items as $item) {
            if (!$dishModel->isDishAvailable($item['dish_id'], $today, $currentDay)) {
                $dish = $dishModel->find($item['dish_id']);
                $validityStatus = $dishModel->getValidityStatus($dish);
                throw new Exception('El platillo "' . $dish['name'] . '" no está disponible: ' . $validityStatus['message']);
            }
        }
        
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
        
        return $orderId;
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
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code
                  FROM {$this->table} o
                  JOIN tables t ON o.table_id = t.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u ON w.user_id = u.id
                  LEFT JOIN tickets tk ON o.id = tk.order_id
                  WHERE o.status = ? AND tk.id IS NULL
                  ORDER BY o.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([ORDER_READY]);
        
        return $stmt->fetchAll();
    }
    
    public function getReadyOrdersGroupedByTable() {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code
                  FROM {$this->table} o
                  JOIN tables t ON o.table_id = t.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u ON w.user_id = u.id
                  LEFT JOIN tickets tk ON o.id = tk.order_id
                  WHERE o.status = ? AND tk.id IS NULL
                  ORDER BY o.table_id ASC, o.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([ORDER_READY]);
        
        $orders = $stmt->fetchAll();
        
        // Group orders by table
        $groupedOrders = [];
        foreach ($orders as $order) {
            $tableId = $order['table_id'];
            if (!isset($groupedOrders[$tableId])) {
                $groupedOrders[$tableId] = [
                    'table_id' => $tableId,
                    'table_number' => $order['table_number'],
                    'orders' => [],
                    'total_amount' => 0,
                    'order_count' => 0
                ];
            }
            $groupedOrders[$tableId]['orders'][] = $order;
            $groupedOrders[$tableId]['total_amount'] += $order['total'];
            $groupedOrders[$tableId]['order_count']++;
        }
        
        return array_values($groupedOrders);
    }
    
    public function getFuturePickupOrders($filters = []) {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
                         COUNT(oi.id) as items_count,
                         COALESCE(SUM(oi.subtotal), 0) as total
                  FROM {$this->table} o
                  LEFT JOIN tables t ON o.table_id = t.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.is_pickup = 1 AND DATE(o.pickup_datetime) > CURDATE()";
        
        $params = [];
        
        if (isset($filters['waiter_id'])) {
            $query .= " AND o.waiter_id = ?";
            $params[] = $filters['waiter_id'];
        }
        
        if (isset($filters['status'])) {
            $query .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        $query .= " GROUP BY o.id ORDER BY o.pickup_datetime ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getTodaysOrders($filters = []) {
        $today = date('Y-m-d');
        
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
                         COUNT(oi.id) as items_count,
                         COALESCE(SUM(oi.subtotal), 0) as total
                  FROM {$this->table} o
                  LEFT JOIN tables t ON o.table_id = t.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE (DATE(o.created_at) = ? OR (o.is_pickup = 1 AND DATE(o.pickup_datetime) = ?))";
        
        $params = [$today, $today];
        
        if (isset($filters['waiter_id'])) {
            $query .= " AND o.waiter_id = ?";
            $params[] = $filters['waiter_id'];
        }
        
        if (isset($filters['status'])) {
            $query .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        // Add search functionality
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query .= " AND (o.customer_name LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ? OR t.number LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function createPublicOrderWithCustomer($orderData, $items, $customerData) {
        try {
            $this->db->beginTransaction();
            
            // Create or find customer
            $customerModel = new Customer();
            $customer = $customerModel->findBy('phone', $customerData['phone']);
            
            if (!$customer) {
                $customerId = $customerModel->create($customerData);
            } else {
                // Update customer info if provided
                if (isset($customerData['name']) && $customerData['name'] !== $customer['name']) {
                    $customerModel->update($customer['id'], ['name' => $customerData['name']]);
                }
                if (isset($customerData['birthday']) && $customerData['birthday'] !== $customer['birthday']) {
                    $customerModel->update($customer['id'], ['birthday' => $customerData['birthday']]);
                }
                $customerId = $customer['id'];
            }
            
            // Add customer_id to order data
            $orderData['customer_id'] = $customerId;
            
            // Create order with items using helper method (no nested transaction)
            $orderId = $this->createOrderWithItemsInTransaction($orderData, $items);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateOrderStatusAndCustomerStats($orderId, $newStatus) {
        try {
            $this->db->beginTransaction();
            
            // Get order with customer info
            $order = $this->find($orderId);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }
            
            // Update order status
            $this->update($orderId, ['status' => $newStatus]);
            
            // Update customer stats if order is completed and has customer
            if ($newStatus === ORDER_DELIVERED && $order['customer_id']) {
                $customerModel = new Customer();
                $customerModel->updateStats($order['customer_id'], $order['total']);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    // ============= EXPIRED ORDERS MANAGEMENT =============
    
    public function getExpiredOrders($filters = []) {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
                         COUNT(oi.id) as items_count,
                         COALESCE(SUM(oi.subtotal), 0) as total,
                         TIMESTAMPDIFF(HOUR, o.created_at, NOW()) as hours_since_created
                  FROM {$this->table} o
                  LEFT JOIN tables t ON o.table_id = t.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.status IN ('pendiente', 'en_preparacion', 'listo')
                  AND DATE(o.created_at) < CURDATE()";
        
        $params = [];
        
        // Apply filters
        if (isset($filters['waiter_id'])) {
            $query .= " AND o.waiter_id = ?";
            $params[] = $filters['waiter_id'];
        }
        
        if (isset($filters['table_id'])) {
            $query .= " AND o.table_id = ?";
            $params[] = $filters['table_id'];
        }
        
        $query .= " GROUP BY o.id
                   ORDER BY o.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getExpiredOrdersCount($waiterId = null) {
        $query = "SELECT COUNT(*) as count 
                  FROM {$this->table} 
                  WHERE status IN ('pendiente', 'en_preparacion', 'listo')
                  AND DATE(created_at) < CURDATE()";
        
        $params = [];
        
        if ($waiterId) {
            $query .= " AND waiter_id = ?";
            $params[] = $waiterId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    public function getExpiredOrdersByTable() {
        $query = "SELECT t.id as table_id, t.number as table_number, 
                         COUNT(o.id) as expired_orders_count,
                         SUM(o.total) as total_pending_amount,
                         MIN(o.created_at) as oldest_order_time
                  FROM tables t
                  INNER JOIN {$this->table} o ON t.id = o.table_id
                  WHERE o.status IN ('pendiente', 'en_preparacion', 'listo')
                  AND DATE(o.created_at) < CURDATE()
                  AND t.active = 1
                  GROUP BY t.id, t.number
                  ORDER BY oldest_order_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getExpiredOrdersReadyForTicket() {
        $query = "SELECT o.*, t.number as table_number, 
                         u.name as waiter_name, w.employee_code,
                         COUNT(oi.id) as items_count,
                         COALESCE(SUM(oi.subtotal), 0) as total,
                         TIMESTAMPDIFF(HOUR, o.created_at, NOW()) as hours_since_created
                  FROM {$this->table} o
                  LEFT JOIN tables t ON o.table_id = t.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.status = ? 
                  AND DATE(o.created_at) < CURDATE()
                  AND o.id NOT IN (SELECT DISTINCT order_id FROM tickets WHERE order_id IS NOT NULL)
                  GROUP BY o.id
                  ORDER BY o.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([ORDER_READY]);
        
        return $stmt->fetchAll();
    }
}
?>