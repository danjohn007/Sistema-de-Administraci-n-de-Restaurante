<?php
class Dish extends BaseModel {
    protected $table = 'dishes';
    public $db; // Make db property public for controller access
    
    public function getDishesByCategory() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE active = 1 
                  ORDER BY category ASC, name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $dishes = $stmt->fetchAll();
        $grouped = [];
        
        foreach ($dishes as $dish) {
            $category = $dish['category'] ?: 'Sin Categoría';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $dish;
        }
        
        return $grouped;
    }
    
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM {$this->table} 
                  WHERE active = 1 AND category IS NOT NULL AND category != '' 
                  ORDER BY category ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return array_column($stmt->fetchAll(), 'category');
    }
    
    public function searchDishes($search, $category = null) {
        $query = "SELECT * FROM {$this->table} WHERE active = 1";
        $params = [];
        
        if ($search) {
            $query .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getPopularDishes($limit = 10) {
        $query = "SELECT d.*, COUNT(oi.dish_id) as order_count,
                         SUM(oi.quantity) as total_quantity
                  FROM {$this->table} d
                  LEFT JOIN order_items oi ON d.id = oi.dish_id
                  WHERE d.active = 1
                  GROUP BY d.id
                  ORDER BY order_count DESC, total_quantity DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    public function getDishStats($dishId) {
        $query = "SELECT 
                    COUNT(DISTINCT oi.order_id) as times_ordered,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.subtotal) as total_revenue,
                    AVG(oi.quantity) as avg_quantity_per_order
                  FROM order_items oi
                  WHERE oi.dish_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dishId]);
        
        return $stmt->fetch();
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
    
    public function updatePrice($dishId, $newPrice) {
        return $this->update($dishId, ['price' => $newPrice]);
    }
    
    // ============= VALIDITY METHODS =============
    
    public function getAvailableDishes($date = null, $dayOfWeek = null) {
        $date = $date ?: date('Y-m-d');
        $dayOfWeek = $dayOfWeek ?: date('N'); // 1 = Monday, 7 = Sunday
        
        $query = "SELECT * FROM {$this->table} 
                  WHERE active = 1 
                  AND (
                      has_validity = FALSE 
                      OR (
                          has_validity = TRUE 
                          AND (validity_start IS NULL OR validity_start <= ?)
                          AND (validity_end IS NULL OR validity_end >= ?)
                          AND availability_days LIKE ?
                      )
                  )
                  ORDER BY category ASC, name ASC";
        
        $params = [$date, $date, '%' . $dayOfWeek . '%'];
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function isDishAvailable($dishId, $date = null, $dayOfWeek = null) {
        $dish = $this->find($dishId);
        if (!$dish || !$dish['active']) {
            return false;
        }
        
        // If dish has no validity restrictions, it's always available
        if (!$dish['has_validity']) {
            return true;
        }
        
        $date = $date ?: date('Y-m-d');
        $dayOfWeek = $dayOfWeek ?: date('N');
        
        // Check date range
        if ($dish['validity_start'] && $date < $dish['validity_start']) {
            return false;
        }
        
        if ($dish['validity_end'] && $date > $dish['validity_end']) {
            return false;
        }
        
        // Check day of week availability
        if ($dish['availability_days'] && strpos($dish['availability_days'], (string)$dayOfWeek) === false) {
            return false;
        }
        
        return true;
    }
    
    public function getAvailabilityDaysArray($availabilityDays) {
        $days = [];
        $dayNames = [
            '1' => 'Lunes',
            '2' => 'Martes', 
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo'
        ];
        
        for ($i = 1; $i <= 7; $i++) {
            if (strpos($availabilityDays, (string)$i) !== false) {
                $days[] = $dayNames[$i];
            }
        }
        
        return $days;
    }
    
    public function getValidityStatus($dish) {
        if (!$dish['has_validity']) {
            return ['status' => 'always_available', 'message' => 'Siempre disponible'];
        }
        
        $today = date('Y-m-d');
        $currentDay = date('N');
        
        // Check if dish is available today
        if (!$this->isDishAvailable($dish['id'], $today, $currentDay)) {
            if ($dish['validity_start'] && $today < $dish['validity_start']) {
                return ['status' => 'not_started', 'message' => 'Disponible desde ' . date('d/m/Y', strtotime($dish['validity_start']))];
            }
            
            if ($dish['validity_end'] && $today > $dish['validity_end']) {
                return ['status' => 'expired', 'message' => 'Venció el ' . date('d/m/Y', strtotime($dish['validity_end']))];
            }
            
            if (strpos($dish['availability_days'], (string)$currentDay) === false) {
                return ['status' => 'not_available_today', 'message' => 'No disponible hoy'];
            }
        }
        
        return ['status' => 'available', 'message' => 'Disponible'];
    }
}
?>