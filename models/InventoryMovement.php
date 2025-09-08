<?php
class InventoryMovement extends BaseModel {
    protected $table = 'inventory_movements';
    
    public function createMovement($data) {
        // Validar datos requeridos
        $required = ['product_id', 'movement_type', 'quantity', 'reference_type', 'user_id', 'movement_date'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Campo requerido: {$field}");
            }
        }
        
        // Calcular costo total
        if (isset($data['cost_per_unit']) && $data['cost_per_unit'] > 0) {
            $data['total_cost'] = $data['quantity'] * $data['cost_per_unit'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Crear el movimiento
            $movementId = $this->create($data);
            
            if ($movementId) {
                // Actualizar el stock del producto
                $this->updateProductStock($data['product_id'], $data['movement_type'], $data['quantity']);
                
                // Actualizar costo promedio si es entrada
                if ($data['movement_type'] === MOVEMENT_TYPE_IN && isset($data['cost_per_unit'])) {
                    $this->updateAverageCost($data['product_id'], $data['quantity'], $data['cost_per_unit']);
                }
            }
            
            $this->db->commit();
            return $movementId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function updateProductStock($productId, $movementType, $quantity) {
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            throw new Exception("Producto no encontrado");
        }
        
        $newStock = $movementType === MOVEMENT_TYPE_IN 
            ? $product['current_stock'] + $quantity 
            : $product['current_stock'] - $quantity;
            
        // No permitir stock negativo
        if ($newStock < 0) {
            throw new Exception("No hay suficiente stock disponible. Stock actual: {$product['current_stock']}, Cantidad solicitada: {$quantity}");
        }
        
        return $productModel->updateStock($productId, $newStock);
    }
    
    private function updateAverageCost($productId, $newQuantity, $newCost) {
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            return false;
        }
        
        $currentStock = $product['current_stock'];
        $currentCost = $product['cost_per_unit'];
        
        // Calcular costo promedio ponderado
        $totalValue = ($currentStock * $currentCost) + ($newQuantity * $newCost);
        $totalQuantity = $currentStock + $newQuantity;
        
        $averageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : $newCost;
        
        return $productModel->update($productId, ['cost_per_unit' => $averageCost]);
    }
    
    public function getMovementsWithDetails($conditions = []) {
        $query = "SELECT 
                    im.*,
                    p.name as product_name,
                    p.unit_measure,
                    p.category as product_category,
                    u.name as user_name
                  FROM {$this->table} im
                  JOIN inventory_products p ON im.product_id = p.id
                  JOIN users u ON im.user_id = u.id";
        
        $params = [];
        $whereClauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'date_from') {
                    $whereClauses[] = "DATE(im.movement_date) >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereClauses[] = "DATE(im.movement_date) <= ?";
                    $params[] = $value;
                } elseif ($field === 'limit') {
                    // Skip 'limit' - será manejado después
                    continue;
                } else {
                    $whereClauses[] = "im.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY im.movement_date DESC, im.created_at DESC";
        
        if (isset($conditions['limit'])) {
            $query .= " LIMIT " . intval($conditions['limit']);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getMovementsByProduct($productId, $limit = 50) {
        return $this->getMovementsWithDetails([
            'product_id' => $productId,
            'limit' => $limit
        ]);
    }
    
    public function getMovementsByType($movementType, $dateFrom = null, $dateTo = null) {
        $conditions = ['movement_type' => $movementType];
        
        if ($dateFrom) {
            $conditions['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions['date_to'] = $dateTo;
        }
        
        return $this->getMovementsWithDetails($conditions);
    }
    
    public function getMovementsByReference($referenceType, $referenceId) {
        return $this->getMovementsWithDetails([
            'reference_type' => $referenceType,
            'reference_id' => $referenceId
        ]);
    }
    
    public function getInventoryReport($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01'); // Primer día del mes actual
        $dateTo = $dateTo ?: date('Y-m-d'); // Hoy
        
        $query = "SELECT 
                    p.name as product_name,
                    p.category,
                    p.unit_measure,
                    p.current_stock,
                    p.cost_per_unit,
                    (p.current_stock * p.cost_per_unit) as stock_value,
                    COALESCE(SUM(CASE WHEN im.movement_type = 'entrada' THEN im.quantity ELSE 0 END), 0) as total_entries,
                    COALESCE(SUM(CASE WHEN im.movement_type = 'salida' THEN im.quantity ELSE 0 END), 0) as total_exits,
                    COALESCE(SUM(CASE WHEN im.movement_type = 'entrada' THEN im.total_cost ELSE 0 END), 0) as total_cost_entries,
                    COALESCE(SUM(CASE WHEN im.movement_type = 'salida' THEN im.total_cost ELSE 0 END), 0) as total_cost_exits
                  FROM inventory_products p
                  LEFT JOIN {$this->table} im ON p.id = im.product_id 
                    AND DATE(im.movement_date) BETWEEN ? AND ?
                  WHERE p.active = 1
                  GROUP BY p.id, p.name, p.category, p.unit_measure, p.current_stock, p.cost_per_unit
                  ORDER BY p.category ASC, p.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function createFromExpense($expenseId, $productId, $quantity, $costPerUnit, $userId) {
        return $this->createMovement([
            'product_id' => $productId,
            'movement_type' => MOVEMENT_TYPE_IN,
            'quantity' => $quantity,
            'cost_per_unit' => $costPerUnit,
            'reference_type' => REFERENCE_TYPE_EXPENSE,
            'reference_id' => $expenseId,
            'description' => 'Entrada por compra registrada en gastos',
            'user_id' => $userId,
            'movement_date' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function createFromTicket($ticketId, $productId, $quantity, $userId) {
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        return $this->createMovement([
            'product_id' => $productId,
            'movement_type' => MOVEMENT_TYPE_OUT,
            'quantity' => $quantity,
            'cost_per_unit' => $product['cost_per_unit'],
            'reference_type' => REFERENCE_TYPE_TICKET,
            'reference_id' => $ticketId,
            'description' => 'Salida por venta en ticket',
            'user_id' => $userId,
            'movement_date' => date('Y-m-d H:i:s')
        ]);
    }
}
?>