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
}
?>