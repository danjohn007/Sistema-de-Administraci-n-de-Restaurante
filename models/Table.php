<?php
class Table extends BaseModel {
    protected $table = 'tables';
    public $db; // Make db property public for controller access
    
    public function getTablesWithWaiters() {
        $query = "SELECT t.*, w.employee_code, u.name as waiter_name 
                  FROM {$this->table} t 
                  LEFT JOIN waiters w ON t.waiter_id = w.id 
                  LEFT JOIN users u ON w.user_id = u.id 
                  WHERE t.active = 1 
                  ORDER BY t.number ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getAvailableTables() {
        return $this->findAll(['status' => TABLE_AVAILABLE, 'active' => 1], 'number ASC');
    }
    
    public function getTablesByStatus($status) {
        return $this->findAll(['status' => $status, 'active' => 1], 'number ASC');
    }
    
    public function updateTableStatus($tableId, $status, $waiterId = null) {
        $data = ['status' => $status];
        
        if ($waiterId !== null) {
            $data['waiter_id'] = $waiterId;
        }
        
        return $this->update($tableId, $data);
    }
    
    public function assignWaiter($tableId, $waiterId) {
        return $this->update($tableId, [
            'waiter_id' => $waiterId,
            'status' => TABLE_OCCUPIED
        ]);
    }
    
    public function freeTable($tableId) {
        return $this->update($tableId, [
            'waiter_id' => null,
            'status' => TABLE_AVAILABLE
        ]);
    }
    
    public function getTableStats() {
        $query = "SELECT 
                    status,
                    COUNT(*) as count,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$this->table} WHERE active = 1)), 2) as percentage
                  FROM {$this->table} 
                  WHERE active = 1 
                  GROUP BY status";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $stats = [];
        $result = $stmt->fetchAll();
        
        foreach ($result as $row) {
            $stats[$row['status']] = [
                'count' => $row['count'],
                'percentage' => $row['percentage']
            ];
        }
        
        return $stats;
    }
    
    public function getCurrentOrder($tableId) {
        $query = "SELECT * FROM orders 
                  WHERE table_id = ? AND status IN ('pendiente', 'en_preparacion', 'listo') 
                  ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$tableId]);
        
        return $stmt->fetch();
    }
    
    public function numberExists($number, $excludeId = null) {
        $query = "SELECT id FROM {$this->table} WHERE number = ? AND active = 1";
        $params = [$number];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    public function getWaiterTables($waiterId) {
        return $this->findAll(['waiter_id' => $waiterId, 'active' => 1], 'number ASC');
    }
    
    public function getAvailableTablesForReservationEdit($reservationId) {
        // Get available tables plus tables already assigned to this reservation
        $query = "SELECT DISTINCT t.* FROM {$this->table} t 
                  WHERE t.active = 1 
                  AND (t.status = ? OR t.id IN (
                      SELECT rt.table_id FROM reservation_tables rt 
                      WHERE rt.reservation_id = ?
                  ))
                  ORDER BY t.number ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([TABLE_AVAILABLE, $reservationId]);
        
        return $stmt->fetchAll();
    }
    
    // ============= DAILY TABLE LIBERATION =============
    
    public function liberateTablesDaily() {
        // Free all tables and set them as available
        $query = "UPDATE {$this->table} 
                  SET status = ?, waiter_id = NULL, updated_at = CURRENT_TIMESTAMP
                  WHERE active = 1";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([TABLE_AVAILABLE]);
    }
    
    public function getTablesWithPendingOrders() {
        // Get tables that have orders that are not delivered (pending closure)
        $query = "SELECT DISTINCT t.*, 
                         COUNT(o.id) as pending_orders_count,
                         MIN(o.created_at) as oldest_order_time,
                         SUM(o.total) as total_pending_amount
                  FROM {$this->table} t
                  INNER JOIN orders o ON t.id = o.table_id
                  WHERE t.active = 1 
                  AND o.status IN ('pendiente', 'en_preparacion', 'listo')
                  AND DATE(o.created_at) < CURDATE()
                  GROUP BY t.id
                  ORDER BY oldest_order_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function markTableWithExpiredOrders($tableId) {
        // Mark table status to indicate it has expired orders
        return $this->update($tableId, [
            'status' => 'cuenta_solicitada' // Reuse existing status to indicate pending closure
        ]);
    }
}
?>