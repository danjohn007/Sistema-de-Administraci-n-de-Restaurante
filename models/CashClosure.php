<?php
class CashClosure extends BaseModel {
    protected $table = 'cash_closures';
    
    public function getClosuresWithDetails($conditions = []) {
        $query = "SELECT 
                    cc.*,
                    b.name as branch_name,
                    u.name as cashier_name
                  FROM {$this->table} cc
                  LEFT JOIN branches b ON cc.branch_id = b.id
                  JOIN users u ON cc.cashier_user_id = u.id";
        
        $params = [];
        $whereClauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'date_from') {
                    $whereClauses[] = "DATE(cc.shift_start) >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereClauses[] = "DATE(cc.shift_end) <= ?";
                    $params[] = $value;
                } elseif ($field === 'limit') {
                    // Skip 'limit' - it should not be part of WHERE clause
                    continue;
                } else {
                    $whereClauses[] = "cc.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY cc.shift_start DESC, cc.created_at DESC";
        
        if (isset($conditions['limit'])) {
            $query .= " LIMIT " . intval($conditions['limit']);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getClosureById($id) {
        $query = "SELECT 
                    cc.*,
                    b.name as branch_name,
                    u.name as cashier_name,
                    u.email as cashier_email
                  FROM {$this->table} cc
                  LEFT JOIN branches b ON cc.branch_id = b.id
                  JOIN users u ON cc.cashier_user_id = u.id
                  WHERE cc.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createClosure($data) {
        // Calculate totals automatically
        $shiftStart = $data['shift_start'];
        $shiftEnd = $data['shift_end'];
        $branchId = $data['branch_id'] ?? null;
        
        // Get sales total for the period
        $salesTotal = $this->getSalesTotal($shiftStart, $shiftEnd, $branchId);
        
        // Get expenses total for the period
        $expensesTotal = $this->getExpensesTotal($shiftStart, $shiftEnd, $branchId);
        
        // Get withdrawals total for the period
        $withdrawalsTotal = $this->getWithdrawalsTotal($shiftStart, $shiftEnd, $branchId);
        
        // Calculate net profit
        $netProfit = $salesTotal - $expensesTotal - $withdrawalsTotal;
        
        // Update data with calculated values
        $data['total_sales'] = $salesTotal;
        $data['total_expenses'] = $expensesTotal;
        $data['total_withdrawals'] = $withdrawalsTotal;
        $data['net_profit'] = $netProfit;
        
        return $this->create($data);
    }
    
    private function getSalesTotal($shiftStart, $shiftEnd, $branchId = null) {
        $query = "SELECT COALESCE(SUM(t.total), 0) as total
                  FROM tickets t
                  JOIN orders o ON t.order_id = o.id";
        
        if ($branchId) {
            $query .= " JOIN tables tb ON o.table_id = tb.id";
        }
        
        $query .= " WHERE t.created_at BETWEEN ? AND ?";
        $params = [$shiftStart, $shiftEnd];
        
        if ($branchId) {
            $query .= " AND tb.branch_id = ?";
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    private function getExpensesTotal($shiftStart, $shiftEnd, $branchId = null) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                  FROM expenses
                  WHERE created_at BETWEEN ? AND ?";
        
        $params = [$shiftStart, $shiftEnd];
        
        if ($branchId) {
            $query .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    private function getWithdrawalsTotal($shiftStart, $shiftEnd, $branchId = null) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                  FROM cash_withdrawals
                  WHERE withdrawal_date BETWEEN ? AND ?";
        
        $params = [$shiftStart, $shiftEnd];
        
        if ($branchId) {
            $query .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    public function getClosuresByDateRange($dateFrom, $dateTo, $branchId = null) {
        $conditions = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        if ($branchId) {
            $conditions['branch_id'] = $branchId;
        }
        
        return $this->getClosuresWithDetails($conditions);
    }
    
    public function getRecentClosures($limit = 10) {
        return $this->getClosuresWithDetails(['limit' => $limit]);
    }
    
    public function getClosuresByUser($userId, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $conditions = [
            'cashier_user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        return $this->getClosuresWithDetails($conditions);
    }
    
    public function getProfitTrend($days = 30, $branchId = null) {
        $query = "SELECT 
                    DATE(shift_start) as closure_date,
                    SUM(net_profit) as daily_profit,
                    SUM(total_sales) as daily_sales
                  FROM {$this->table}
                  WHERE shift_start >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($branchId) {
            $query .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $query .= " GROUP BY DATE(shift_start)
                   ORDER BY closure_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>