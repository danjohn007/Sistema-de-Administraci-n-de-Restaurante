<?php
class Customer extends BaseModel {
    protected $table = 'customers';
    
    public function findById($id) {
        return $this->find($id);
    }
    
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
        $query = "SELECT * FROM customers WHERE (birthday_day IS NOT NULL AND birthday_month IS NOT NULL)";
        $params = [];
        
        if ($month) {
            $query .= " AND birthday_month = ?";
            $params[] = $month;
        }
        
        if ($day) {
            $query .= " AND birthday_day = ?";
            $params[] = $day;
        }
        
        $query .= " ORDER BY birthday_month, birthday_day";
        
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
    
    public function revertStats($customerId, $orderTotal) {
        $query = "UPDATE customers 
                  SET total_visits = GREATEST(total_visits - 1, 0), 
                      total_spent = GREATEST(total_spent - ?, 0),
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$orderTotal, $customerId]);
    }
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
    
    public function searchCustomers($query) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT * FROM customers 
                WHERE active = 1 
                AND (name LIKE ? OR phone LIKE ?) 
                ORDER BY total_visits DESC, name ASC 
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll();
    }
    
    public function findOrCreateByPhone($customerData) {
        // Try to find existing customer by phone
        $existing = $this->findBy('phone', $customerData['phone']);
        
        if ($existing) {
            // Update name if provided and different
            if (isset($customerData['name']) && $customerData['name'] !== $existing['name']) {
                $this->update($existing['id'], ['name' => $customerData['name']]);
            }
            return $existing['id'];
        }
        
        // Create new customer
        return $this->create($customerData);
    }
    
    /**
     * Parse birthday in DD/MM format and return array with day and month
     * @param string $birthday in DD/MM format (e.g., "15/03")
     * @return array|null ['day' => int, 'month' => int] or null if invalid
     */
    public function parseBirthday($birthday) {
        if (empty($birthday)) {
            return null;
        }
        
        // Validate DD/MM format
        if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])$/', $birthday)) {
            return null;
        }
        
        list($day, $month) = explode('/', $birthday);
        $day = intval($day);
        $month = intval($month);
        
        // Validate that it's a valid date combination
        if (!checkdate($month, $day, 2000)) {
            return null;
        }
        
        return ['day' => $day, 'month' => $month];
    }
    
    /**
     * Override create method to handle birthday parsing
     */
    public function create($data) {
        // Parse birthday if provided
        if (isset($data['birthday']) && !empty($data['birthday'])) {
            $birthdayData = $this->parseBirthday($data['birthday']);
            if ($birthdayData) {
                $data['birthday_day'] = $birthdayData['day'];
                $data['birthday_month'] = $birthdayData['month'];
            }
            // Remove the original birthday field since we're using separate columns
            unset($data['birthday']);
        }
        
        return parent::create($data);
    }
    
    /**
     * Override update method to handle birthday parsing
     */
    public function update($id, $data) {
        // Parse birthday if provided
        if (isset($data['birthday']) && !empty($data['birthday'])) {
            $birthdayData = $this->parseBirthday($data['birthday']);
            if ($birthdayData) {
                $data['birthday_day'] = $birthdayData['day'];
                $data['birthday_month'] = $birthdayData['month'];
            }
            // Remove the original birthday field since we're using separate columns
            unset($data['birthday']);
        } else if (isset($data['birthday']) && empty($data['birthday'])) {
            // If birthday is empty, clear the day and month fields
            $data['birthday_day'] = null;
            $data['birthday_month'] = null;
            unset($data['birthday']);
        }
        
        return parent::update($id, $data);
    }
    
    public function getAllWithPagination($limit = 20, $offset = 0) {
        $query = "SELECT * FROM customers 
                  WHERE active = 1 
                  ORDER BY total_visits DESC, total_spent DESC, name ASC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as count FROM customers WHERE active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    public function getMonthlyCustomerStats($startDate, $endDate) {
        $query = "SELECT 
                    DATE_FORMAT(o.created_at, '%Y-%m') as month,
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    COUNT(o.id) as total_orders,
                    SUM(o.total) as total_revenue,
                    AVG(o.total) as avg_order_value
                  FROM orders o 
                  WHERE o.customer_id IS NOT NULL 
                    AND DATE(o.created_at) BETWEEN ? AND ?
                    AND o.status = 'entregado'
                  GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                  ORDER BY month DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
    
    public function getCustomerGrowthData() {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_customers
                  FROM customers 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}