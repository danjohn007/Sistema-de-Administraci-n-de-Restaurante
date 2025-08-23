<?php
class Expense extends BaseModel {
    protected $table = 'expenses';
    
    public function getExpensesWithDetails($conditions = []) {
        $query = "SELECT 
                    e.*,
                    ec.name as category_name,
                    ec.color as category_color,
                    b.name as branch_name,
                    u.name as user_name
                  FROM {$this->table} e
                  JOIN expense_categories ec ON e.category_id = ec.id
                  LEFT JOIN branches b ON e.branch_id = b.id
                  JOIN users u ON e.user_id = u.id";
        
        $params = [];
        $whereClauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'date_from') {
                    $whereClauses[] = "e.expense_date >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereClauses[] = "e.expense_date <= ?";
                    $params[] = $value;
                } elseif ($field === 'limit') {
                    // Skip 'limit' - it should not be part of WHERE clause
                    continue;
                } else {
                    $whereClauses[] = "e.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY e.expense_date DESC, e.created_at DESC";
        
        if (isset($conditions['limit'])) {
            $query .= " LIMIT " . intval($conditions['limit']);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getExpenseById($id) {
        $query = "SELECT 
                    e.*,
                    ec.name as category_name,
                    ec.color as category_color,
                    b.name as branch_name,
                    u.name as user_name
                  FROM {$this->table} e
                  JOIN expense_categories ec ON e.category_id = ec.id
                  LEFT JOIN branches b ON e.branch_id = b.id
                  JOIN users u ON e.user_id = u.id
                  WHERE e.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createExpense($data) {
        return $this->create($data);
    }
    
    public function updateExpense($id, $data) {
        return $this->update($id, $data);
    }
    
    public function getExpensesByDateRange($dateFrom, $dateTo, $branchId = null, $categoryId = null) {
        $query = "SELECT 
                    e.*,
                    ec.name as category_name,
                    ec.color as category_color,
                    b.name as branch_name,
                    u.name as user_name
                  FROM {$this->table} e
                  JOIN expense_categories ec ON e.category_id = ec.id
                  LEFT JOIN branches b ON e.branch_id = b.id
                  JOIN users u ON e.user_id = u.id
                  WHERE e.expense_date BETWEEN ? AND ?";
        
        $params = [$dateFrom, $dateTo];
        
        if ($branchId) {
            $query .= " AND e.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($categoryId) {
            $query .= " AND e.category_id = ?";
            $params[] = $categoryId;
        }
        
        $query .= " ORDER BY e.expense_date DESC, e.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getTotalByCategory($dateFrom = null, $dateTo = null, $branchId = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    ec.name as category_name,
                    ec.color as category_color,
                    COALESCE(SUM(e.amount), 0) as total_amount,
                    COUNT(e.id) as expense_count
                  FROM expense_categories ec
                  LEFT JOIN {$this->table} e ON ec.id = e.category_id 
                    AND e.expense_date BETWEEN ? AND ?";
        
        $params = [$dateFrom, $dateTo];
        
        if ($branchId) {
            $query .= " AND e.branch_id = ?";
            $params[] = $branchId;
        }
        
        $query .= " WHERE ec.active = 1
                   GROUP BY ec.id, ec.name, ec.color
                   ORDER BY total_amount DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getDailyExpenses($dateFrom = null, $dateTo = null, $branchId = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    e.expense_date,
                    COALESCE(SUM(e.amount), 0) as total_amount,
                    COUNT(e.id) as expense_count
                  FROM {$this->table} e
                  WHERE e.expense_date BETWEEN ? AND ?";
        
        $params = [$dateFrom, $dateTo];
        
        if ($branchId) {
            $query .= " AND e.branch_id = ?";
            $params[] = $branchId;
        }
        
        $query .= " GROUP BY e.expense_date
                   ORDER BY e.expense_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function deleteExpense($id) {
        // First get the expense to delete associated file
        $expense = $this->find($id);
        if ($expense && $expense['receipt_file']) {
            $filePath = UPLOAD_EVIDENCE_PATH . $expense['receipt_file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $this->delete($id);
    }
}
?>