<?php
class Customer extends BaseModel {
    protected $table = 'customers';
    
    public function getTopCustomersByVisits($limit = 10) {
        $query = "SELECT * FROM customers 
                  WHERE total_visits > 0 
                  ORDER BY total_visits DESC, total_spent DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    public function getTopCustomersBySpending($limit = 10) {
        $query = "SELECT * FROM customers 
                  WHERE total_spent > 0 
                  ORDER BY total_spent DESC, total_visits DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    public function getBirthdayCustomers($month = null, $day = null) {
        $query = "SELECT * FROM customers WHERE birthday IS NOT NULL";
        $params = [];
        
        if ($month) {
            $query .= " AND MONTH(birthday) = ?";
            $params[] = $month;
        }
        
        if ($day) {
            $query .= " AND DAY(birthday) = ?";
            $params[] = $day;
        }
        
        $query .= " ORDER BY MONTH(birthday), DAY(birthday)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function updateStats($customerId, $orderTotal) {
        $query = "UPDATE customers 
                  SET total_visits = total_visits + 1, 
                      total_spent = total_spent + ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$orderTotal, $customerId]);
    }
    
    public function getCustomerWithStats($customerId) {
        $query = "SELECT c.*, 
                         COUNT(o.id) as order_count,
                         AVG(o.total) as avg_order_value,
                         MAX(o.created_at) as last_visit
                  FROM customers c 
                  LEFT JOIN orders o ON c.id = o.customer_id 
                  WHERE c.id = ?
                  GROUP BY c.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$customerId]);
        
        return $stmt->fetch();
    }
}