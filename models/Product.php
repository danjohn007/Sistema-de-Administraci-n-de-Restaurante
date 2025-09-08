<?php
class Product extends BaseModel {
    protected $table = 'inventory_products';
    
    public function getProductsByCategory() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE active = 1 
                  ORDER BY category ASC, name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $products = $stmt->fetchAll();
        $grouped = [];
        
        foreach ($products as $product) {
            $category = $product['category'] ?: 'Sin Categoría';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $product;
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
    
    public function getLowStockProducts() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE active = 1 AND current_stock <= min_stock 
                  ORDER BY (current_stock - min_stock) ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getProductsWithStock() {
        $query = "SELECT *, 
                         CASE 
                             WHEN current_stock <= min_stock THEN 'low'
                             WHEN current_stock >= max_stock THEN 'high'
                             ELSE 'normal'
                         END as stock_status
                  FROM {$this->table} 
                  WHERE active = 1 
                  ORDER BY category ASC, name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function updateStock($productId, $newStock) {
        return $this->update($productId, [
            'current_stock' => $newStock,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function adjustStock($productId, $quantity, $operation = 'add') {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }
        
        $newStock = $operation === 'add' 
            ? $product['current_stock'] + $quantity 
            : $product['current_stock'] - $quantity;
            
        // No permitir stock negativo
        $newStock = max(0, $newStock);
        
        return $this->updateStock($productId, $newStock);
    }
    
    public function searchProducts($search, $category = null) {
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
    
    public function getIngredientProducts() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE active = 1 AND is_dish_ingredient = 1 
                  ORDER BY category ASC, name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getProductMovements($productId, $limit = 50) {
        $query = "SELECT im.*, u.name as user_name 
                  FROM inventory_movements im
                  JOIN users u ON im.user_id = u.id
                  WHERE im.product_id = ?
                  ORDER BY im.movement_date DESC, im.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$productId, $limit]);
        
        return $stmt->fetchAll();
    }
    
    public function getInventoryValue() {
        $query = "SELECT SUM(current_stock * cost_per_unit) as total_value
                  FROM {$this->table} 
                  WHERE active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total_value'] ?: 0;
    }
    
    public function getAll() {
        return $this->findAll(['active' => 1], 'name ASC');
    }
}
?>