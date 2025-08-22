<?php
class Waiter extends BaseModel {
    protected $table = 'waiters';
    
    public function getWaitersWithUsers() {
        $query = "SELECT w.*, u.name, u.email, u.active as user_active 
                  FROM {$this->table} w 
                  JOIN users u ON w.user_id = u.id 
                  WHERE w.active = 1 
                  ORDER BY u.name ASC";
        
        $stmt = $this->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getWaiterByUserId($userId) {
        return $this->findBy('user_id', $userId);
    }
    
    public function createWaiter($userData, $waiterData) {
        try {
            $this->beginTransaction();
            
            // Create user first
            $userModel = new User();
            $userId = $userModel->createUser($userData);
            
            if (!$userId) {
                throw new Exception('Error al crear usuario');
            }
            
            // Create waiter
            $waiterData['user_id'] = $userId;
            $waiterId = $this->create($waiterData);
            
            if (!$waiterId) {
                throw new Exception('Error al crear mesero');
            }
            
            $this->commit();
            return $waiterId;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    public function getWaiterOrders($waiterId, $startDate = null, $endDate = null) {
        $query = "SELECT o.*, t.number as table_number, 
                         COUNT(oi.id) as items_count,
                         SUM(oi.subtotal) as total_amount
                  FROM orders o 
                  JOIN tables t ON o.table_id = t.id 
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.waiter_id = ?";
        
        $params = [$waiterId];
        
        if ($startDate) {
            $query .= " AND DATE(o.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $query .= " AND DATE(o.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getWaiterStats($waiterId, $period = 'today') {
        $dateCondition = '';
        $params = [$waiterId];
        
        switch ($period) {
            case 'today':
                $dateCondition = "AND DATE(o.created_at) = CURDATE()";
                break;
            case 'week':
                $dateCondition = "AND WEEK(o.created_at) = WEEK(NOW())";
                break;
            case 'month':
                $dateCondition = "AND MONTH(o.created_at) = MONTH(NOW())";
                break;
        }
        
        $query = "SELECT 
                    COUNT(DISTINCT o.id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_sales,
                    COUNT(DISTINCT o.table_id) as tables_served,
                    AVG(o.total) as average_order
                  FROM orders o 
                  WHERE o.waiter_id = ? {$dateCondition}";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
}
?>