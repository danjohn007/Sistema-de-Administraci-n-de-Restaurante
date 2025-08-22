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
}
?>