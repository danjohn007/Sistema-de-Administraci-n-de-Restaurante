<?php
class Branch extends BaseModel {
    protected $table = 'branches';
    
    public function getAllActive() {
        return $this->findAll(['active' => 1], 'name ASC');
    }
    
    public function getBranchWithManager($id) {
        $query = "SELECT b.*, u.name as manager_name, u.email as manager_email
                  FROM {$this->table} b
                  LEFT JOIN users u ON b.manager_user_id = u.id
                  WHERE b.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getBranchStaff($branchId) {
        $query = "SELECT bs.*, u.name, u.email, u.role as system_role
                  FROM branch_staff bs
                  JOIN users u ON bs.user_id = u.id
                  WHERE bs.branch_id = ? AND bs.active = 1
                  ORDER BY bs.role, u.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }
    
    public function assignStaff($branchId, $userId, $role) {
        // Check if already assigned
        $query = "SELECT id FROM branch_staff WHERE branch_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$branchId, $userId]);
        
        if ($stmt->fetch()) {
            // Update existing assignment
            $query = "UPDATE branch_staff SET role = ?, active = 1 WHERE branch_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$role, $branchId, $userId]);
        } else {
            // Create new assignment
            $query = "INSERT INTO branch_staff (branch_id, user_id, role) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$branchId, $userId, $role]);
        }
    }
    
    public function removeStaff($branchId, $userId) {
        $query = "UPDATE branch_staff SET active = 0 WHERE branch_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$branchId, $userId]);
    }
    
    public function getUserBranches($userId) {
        $query = "SELECT b.*, bs.role as user_role
                  FROM branches b
                  JOIN branch_staff bs ON b.id = bs.branch_id
                  WHERE bs.user_id = ? AND bs.active = 1 AND b.active = 1
                  ORDER BY b.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getBranchStats($branchId, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        // Get sales statistics
        $salesQuery = "SELECT 
                        COUNT(t.id) as total_tickets,
                        COALESCE(SUM(t.total), 0) as total_sales
                       FROM tickets t
                       JOIN orders o ON t.order_id = o.id
                       JOIN tables tb ON o.table_id = tb.id
                       WHERE DATE(t.created_at) BETWEEN ? AND ?";
        
        if ($branchId > 0) {
            $salesQuery .= " AND tb.branch_id = ?";
            $salesParams = [$dateFrom, $dateTo, $branchId];
        } else {
            $salesParams = [$dateFrom, $dateTo];
        }
        
        $stmt = $this->db->prepare($salesQuery);
        $stmt->execute($salesParams);
        $salesStats = $stmt->fetch();
        
        // Get expense statistics
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total_expenses
                         FROM expenses
                         WHERE expense_date BETWEEN ? AND ?";
        
        if ($branchId > 0) {
            $expenseQuery .= " AND branch_id = ?";
            $expenseParams = [$dateFrom, $dateTo, $branchId];
        } else {
            $expenseParams = [$dateFrom, $dateTo];
        }
        
        $stmt = $this->db->prepare($expenseQuery);
        $stmt->execute($expenseParams);
        $expenseStats = $stmt->fetch();
        
        // Get withdrawal statistics
        $withdrawalQuery = "SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                           FROM cash_withdrawals
                           WHERE DATE(withdrawal_date) BETWEEN ? AND ?";
        
        if ($branchId > 0) {
            $withdrawalQuery .= " AND branch_id = ?";
            $withdrawalParams = [$dateFrom, $dateTo, $branchId];
        } else {
            $withdrawalParams = [$dateFrom, $dateTo];
        }
        
        $stmt = $this->db->prepare($withdrawalQuery);
        $stmt->execute($withdrawalParams);
        $withdrawalStats = $stmt->fetch();
        
        return [
            'total_sales' => $salesStats['total_sales'] ?? 0,
            'total_tickets' => $salesStats['total_tickets'] ?? 0,
            'total_expenses' => $expenseStats['total_expenses'] ?? 0,
            'total_withdrawals' => $withdrawalStats['total_withdrawals'] ?? 0,
            'net_profit' => ($salesStats['total_sales'] ?? 0) - ($expenseStats['total_expenses'] ?? 0) - ($withdrawalStats['total_withdrawals'] ?? 0)
        ];
    }
}
?>