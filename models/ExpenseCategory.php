<?php
class ExpenseCategory extends BaseModel {
    protected $table = 'expense_categories';
    
    public function getAllActive() {
        return $this->findAll(['active' => 1], 'name ASC');
    }
    
    public function getCategoryWithExpenseStats($id, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    ec.*,
                    COUNT(e.id) as total_expenses,
                    COALESCE(SUM(e.amount), 0) as total_amount
                  FROM {$this->table} ec
                  LEFT JOIN expenses e ON ec.id = e.category_id 
                    AND e.expense_date BETWEEN ? AND ?
                  WHERE ec.id = ?
                  GROUP BY ec.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo, $id]);
        return $stmt->fetch();
    }
    
    public function getCategoriesWithStats($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    ec.*,
                    COUNT(e.id) as total_expenses,
                    COALESCE(SUM(e.amount), 0) as total_amount
                  FROM {$this->table} ec
                  LEFT JOIN expenses e ON ec.id = e.category_id 
                    AND e.expense_date BETWEEN ? AND ?
                  WHERE ec.active = 1
                  GROUP BY ec.id
                  ORDER BY ec.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function nameExists($name, $excludeId = null) {
        $query = "SELECT id FROM {$this->table} WHERE name = ? AND active = 1";
        $params = [$name];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    public function getTopCategories($limit = 5, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // First day of current month
        $dateTo = $dateTo ?: date('Y-m-d'); // Today
        
        $query = "SELECT 
                    ec.name,
                    ec.color,
                    COALESCE(SUM(e.amount), 0) as total_amount,
                    COUNT(e.id) as expense_count
                  FROM {$this->table} ec
                  LEFT JOIN expenses e ON ec.id = e.category_id 
                    AND e.expense_date BETWEEN ? AND ?
                  WHERE ec.active = 1
                  GROUP BY ec.id, ec.name, ec.color
                  ORDER BY total_amount DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo, $limit]);
        return $stmt->fetchAll();
    }
}
?>