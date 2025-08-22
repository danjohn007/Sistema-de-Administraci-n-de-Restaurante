<?php
class OrderItem extends BaseModel {
    protected $table = 'order_items';
    
    public function getItemsByOrder($orderId) {
        $query = "SELECT oi.*, d.name as dish_name, d.category, d.price as current_price
                  FROM {$this->table} oi
                  JOIN dishes d ON oi.dish_id = d.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$orderId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateQuantity($itemId, $quantity) {
        $item = $this->find($itemId);
        if (!$item) {
            return false;
        }
        
        $subtotal = $quantity * $item['unit_price'];
        
        return $this->update($itemId, [
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ]);
    }
    
    public function getTopSellingItems($limit = 10, $period = null) {
        $query = "SELECT d.name, d.category, d.price,
                         SUM(oi.quantity) as total_quantity,
                         COUNT(DISTINCT oi.order_id) as times_ordered,
                         SUM(oi.subtotal) as total_revenue
                  FROM {$this->table} oi
                  JOIN dishes d ON oi.dish_id = d.id
                  JOIN orders o ON oi.order_id = o.id";
        
        $params = [];
        
        if ($period) {
            switch ($period) {
                case 'today':
                    $query .= " WHERE DATE(o.created_at) = CURDATE()";
                    break;
                case 'week':
                    $query .= " WHERE WEEK(o.created_at) = WEEK(NOW())";
                    break;
                case 'month':
                    $query .= " WHERE MONTH(o.created_at) = MONTH(NOW())";
                    break;
            }
        }
        
        $query .= " GROUP BY oi.dish_id, d.name, d.category, d.price
                   ORDER BY total_quantity DESC
                   LIMIT ?";
        
        $params[] = $limit;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}
?>