<?php
class CashWithdrawal extends BaseModel {
    protected $table = 'cash_withdrawals';
    
    public function getWithdrawalsWithDetails($conditions = []) {
        $query = "SELECT 
                    cw.*,
                    b.name as branch_name,
                    u1.name as responsible_name,
                    u2.name as authorized_by_name
                  FROM {$this->table} cw
                  LEFT JOIN branches b ON cw.branch_id = b.id
                  JOIN users u1 ON cw.responsible_user_id = u1.id
                  LEFT JOIN users u2 ON cw.authorized_by_user_id = u2.id";
        
        $params = [];
        $whereClauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'date_from') {
                    $whereClauses[] = "DATE(cw.withdrawal_date) >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereClauses[] = "DATE(cw.withdrawal_date) <= ?";
                    $params[] = $value;
                } elseif ($field === 'limit') {
                    // Skip 'limit' - it should not be part of WHERE clause
                    continue;
                } else {
                    $whereClauses[] = "cw.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY cw.withdrawal_date DESC, cw.created_at DESC";
        
        if (isset($conditions['limit'])) {
            $query .= " LIMIT " . intval($conditions['limit']);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getWithdrawalById($id) {
        $query = "SELECT 
                    cw.*,
                    b.name as branch_name,
                    u1.name as responsible_name,
                    u1.email as responsible_email,
                    u2.name as authorized_by_name,
                    u2.email as authorized_by_email
                  FROM {$this->table} cw
                  LEFT JOIN branches b ON cw.branch_id = b.id
                  JOIN users u1 ON cw.responsible_user_id = u1.id
                  LEFT JOIN users u2 ON cw.authorized_by_user_id = u2.id
                  WHERE cw.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createWithdrawal($data) {
        return $this->create($data);
    }
    
    public function authorizeWithdrawal($id, $authorizedByUserId) {
        return $this->update($id, ['authorized_by_user_id' => $authorizedByUserId]);
    }
    
    public function getWithdrawalsByDateRange($dateFrom, $dateTo, $branchId = null) {
        $query = "SELECT 
                    cw.*,
                    b.name as branch_name,
                    u1.name as responsible_name,
                    u2.name as authorized_by_name
                  FROM {$this->table} cw
                  LEFT JOIN branches b ON cw.branch_id = b.id
                  JOIN users u1 ON cw.responsible_user_id = u1.id
                  LEFT JOIN users u2 ON cw.authorized_by_user_id = u2.id
                  WHERE DATE(cw.withdrawal_date) BETWEEN ? AND ?";
        
        $params = [$dateFrom, $dateTo];
        
        if ($branchId) {
            $query .= " AND cw.branch_id = ?";
            $params[] = $branchId;
        }
        
        $query .= " ORDER BY cw.withdrawal_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getTotalWithdrawals($dateFrom = null, $dateTo = null, $branchId = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    COALESCE(SUM(amount), 0) as total_amount,
                    COUNT(id) as total_count
                  FROM {$this->table}
                  WHERE DATE(withdrawal_date) BETWEEN ? AND ?";
        
        $params = [$dateFrom, $dateTo];
        
        if ($branchId) {
            $query .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function getRecentWithdrawals($limit = 10) {
        return $this->getWithdrawalsWithDetails(['limit' => $limit]);
    }
    
    public function getPendingAuthorizations() {
        return $this->getWithdrawalsWithDetails(['authorized_by_user_id' => null]);
    }
    
    public function deleteWithdrawal($id) {
        // First get the withdrawal to delete associated file
        $withdrawal = $this->find($id);
        if ($withdrawal && $withdrawal['evidence_file']) {
            $filePath = UPLOAD_EVIDENCE_PATH . $withdrawal['evidence_file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $this->delete($id);
    }
    
    public function getWithdrawalsByUser($userId, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $conditions = [
            'responsible_user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        return $this->getWithdrawalsWithDetails($conditions);
    }
}
?>