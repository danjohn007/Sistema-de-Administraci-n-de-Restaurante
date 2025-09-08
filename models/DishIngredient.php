<?php
class DishIngredient extends BaseModel {
    protected $table = 'dish_ingredients';
    
    public function getIngredientsByDish($dishId) {
        $query = "SELECT 
                    di.*,
                    p.name as product_name,
                    p.unit_measure,
                    p.current_stock,
                    p.cost_per_unit,
                    (di.quantity_needed * p.cost_per_unit) as ingredient_cost
                  FROM {$this->table} di
                  JOIN inventory_products p ON di.product_id = p.id
                  WHERE di.dish_id = ? AND p.active = 1
                  ORDER BY p.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dishId]);
        
        return $stmt->fetchAll();
    }
    
    public function getDishesByIngredient($productId) {
        $query = "SELECT 
                    di.*,
                    d.name as dish_name,
                    d.price as dish_price,
                    d.category as dish_category
                  FROM {$this->table} di
                  JOIN dishes d ON di.dish_id = d.id
                  WHERE di.product_id = ? AND d.active = 1
                  ORDER BY d.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$productId]);
        
        return $stmt->fetchAll();
    }
    
    public function addIngredientToDish($dishId, $productId, $quantity) {
        // Verificar si ya existe la relación
        $existing = $this->findByFields([
            'dish_id' => $dishId,
            'product_id' => $productId
        ]);
        
        if ($existing) {
            // Actualizar cantidad existente
            return $this->update($existing['id'], [
                'quantity_needed' => $quantity
            ]);
        } else {
            // Crear nueva relación
            return $this->create([
                'dish_id' => $dishId,
                'product_id' => $productId,
                'quantity_needed' => $quantity
            ]);
        }
    }
    
    public function removeIngredientFromDish($dishId, $productId) {
        $query = "DELETE FROM {$this->table} 
                  WHERE dish_id = ? AND product_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$dishId, $productId]);
    }
    
    public function getDishCost($dishId) {
        $query = "SELECT SUM(di.quantity_needed * p.cost_per_unit) as total_cost
                  FROM {$this->table} di
                  JOIN inventory_products p ON di.product_id = p.id
                  WHERE di.dish_id = ? AND p.active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dishId]);
        
        $result = $stmt->fetch();
        return $result['total_cost'] ?: 0;
    }
    
    public function canPrepareDish($dishId, $quantity = 1) {
        $ingredients = $this->getIngredientsByDish($dishId);
        $canPrepare = true;
        $missingIngredients = [];
        
        foreach ($ingredients as $ingredient) {
            $neededQuantity = $ingredient['quantity_needed'] * $quantity;
            $availableStock = $ingredient['current_stock'];
            
            if ($availableStock < $neededQuantity) {
                $canPrepare = false;
                $missingIngredients[] = [
                    'product_name' => $ingredient['product_name'],
                    'needed' => $neededQuantity,
                    'available' => $availableStock,
                    'missing' => $neededQuantity - $availableStock,
                    'unit_measure' => $ingredient['unit_measure']
                ];
            }
        }
        
        return [
            'can_prepare' => $canPrepare,
            'missing_ingredients' => $missingIngredients
        ];
    }
    
    public function deductIngredientsForDish($dishId, $quantity, $userId, $ticketId = null) {
        $ingredients = $this->getIngredientsByDish($dishId);
        $movementModel = new InventoryMovement();
        
        // Check if we need to manage our own transaction
        $shouldManageTransaction = !$this->db->getConnection()->inTransaction();
        
        try {
            if ($shouldManageTransaction) {
                $this->db->beginTransaction();
            }
            
            foreach ($ingredients as $ingredient) {
                $neededQuantity = $ingredient['quantity_needed'] * $quantity;
                
                // Crear movimiento de salida
                $movementModel->createMovement([
                    'product_id' => $ingredient['product_id'],
                    'movement_type' => MOVEMENT_TYPE_OUT,
                    'quantity' => $neededQuantity,
                    'cost_per_unit' => $ingredient['cost_per_unit'],
                    'reference_type' => $ticketId ? REFERENCE_TYPE_TICKET : REFERENCE_TYPE_MANUAL,
                    'reference_id' => $ticketId,
                    'description' => "Descuento por preparación de platillo: {$ingredient['product_name']}",
                    'user_id' => $userId,
                    'movement_date' => date('Y-m-d H:i:s')
                ]);
            }
            
            if ($shouldManageTransaction) {
                $this->db->commit();
            }
            return true;
            
        } catch (Exception $e) {
            if ($shouldManageTransaction) {
                $this->db->rollback();
            }
            throw $e;
        }
    }
    
    public function getRecipeAnalysis($dishId) {
        $ingredients = $this->getIngredientsByDish($dishId);
        $totalCost = 0;
        $lowStockIngredients = [];
        
        foreach ($ingredients as $ingredient) {
            $totalCost += $ingredient['ingredient_cost'];
            
            // Verificar si el ingrediente tiene stock bajo
            if ($ingredient['current_stock'] <= ($ingredient['quantity_needed'] * 5)) { // Si solo puede hacer 5 platillos o menos
                $lowStockIngredients[] = $ingredient;
            }
        }
        
        return [
            'total_cost' => $totalCost,
            'ingredient_count' => count($ingredients),
            'low_stock_ingredients' => $lowStockIngredients,
            'ingredients' => $ingredients
        ];
    }
    
    public function findByFields($fields) {
        $whereClauses = [];
        $params = [];
        
        foreach ($fields as $field => $value) {
            $whereClauses[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
}
?>