<?php
class Ticket extends BaseModel {
    protected $table = 'tickets';
    
    public function generateTicketNumber() {
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $sequential = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return "T{$date}{$sequential}";
    }
    
    public function createTicket($orderId, $cashierId, $paymentMethod = 'efectivo') {
        try {
            $this->db->beginTransaction();
            
            // Get order details
            $orderModel = new Order();
            $order = $orderModel->find($orderId);
            
            if (!$order) {
                throw new Exception('Orden no encontrada');
            }
            
            if ($order['status'] !== ORDER_READY) {
                throw new Exception('El pedido debe estar en estado "Listo" para generar el ticket');
            }
            
            // Check if order already has a ticket
            $existingTicket = $this->findBy('order_id', $orderId);
            if ($existingTicket) {
                throw new Exception('Este pedido ya tiene un ticket generado');
            }
            
            // Validate order is from today (cannot close orders from previous days without proper process)
            $orderDate = date('Y-m-d', strtotime($order['created_at']));
            $today = date('Y-m-d');
            if ($orderDate !== $today) {
                throw new Exception('Solo se pueden cerrar pedidos del día actual');
            }
            
            // Calculate totals with proper rounding
            // Prices already include 16% IVA, so we need to separate it
            $totalWithTax = floatval($order['total']);
            $subtotal = round($totalWithTax / 1.16, 2); // Remove 16% IVA to get subtotal
            $tax = round($totalWithTax - $subtotal, 2); // Calculate the IVA amount
            $total = $totalWithTax; // Total remains the same
            
            // Validate data before insertion
            if ($subtotal <= 0) {
                throw new Exception('El subtotal debe ser mayor a cero');
            }
            
            if (!in_array($paymentMethod, ['efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar'])) {
                throw new Exception('Método de pago inválido');
            }
            
            // Create ticket
            $ticketData = [
                'order_id' => intval($orderId),
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => intval($cashierId),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod
            ];
            
            // Log ticket creation attempt for debugging
            error_log("Ticket creation attempt: " . json_encode($ticketData));
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket en la base de datos');
            }
            
            // Update order status and customer stats
            $orderModel->updateOrderStatusAndCustomerStats($orderId, ORDER_DELIVERED);
            
            // Update customer statistics if order has a customer
            if ($order['customer_id']) {
                $customerModel = new Customer();
                $customerModel->updateStats($order['customer_id'], $order['total']);
            }
            
            // Deduct inventory if enabled and auto-deduct is on
            $this->deductInventoryForTicket($ticketId, $orderId, $cashierId);
            
            // Free the table (set to available) since the ticket has been generated
            $tableModel = new Table();
            $tableModel->updateTableStatus($order['table_id'], TABLE_AVAILABLE);
            
            $this->db->commit();
            error_log("Ticket created successfully with ID: $ticketId");
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Ticket creation failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function createTicketFromMultipleOrders($orderIds, $cashierId, $paymentMethod = 'efectivo') {
        try {
            $this->db->beginTransaction();
            
            // Get order details and validate they're all from the same table, same day, and same waiter
            $orderModel = new Order();
            $orders = [];
            $tableId = null;
            $waiterId = null;
            $orderDate = null;
            $totalSubtotal = 0;
            
            foreach ($orderIds as $orderId) {
                $order = $orderModel->find($orderId);
                if (!$order) {
                    throw new Exception("Orden {$orderId} no encontrada");
                }
                
                if ($order['status'] !== ORDER_READY) {
                    throw new Exception("La orden {$orderId} no está en estado 'Listo'");
                }
                
                // Check if order already has a ticket
                $existingTicket = $this->findBy('order_id', $orderId);
                if ($existingTicket) {
                    throw new Exception("La orden {$orderId} ya tiene un ticket generado");
                }
                
                // Validate all orders are from the same table
                if ($tableId === null) {
                    $tableId = $order['table_id'];
                } elseif ($tableId !== $order['table_id']) {
                    throw new Exception('Todas las órdenes deben ser de la misma mesa');
                }
                
                // Validate all orders are from the same waiter
                if ($waiterId === null) {
                    $waiterId = $order['waiter_id'];
                } elseif ($waiterId !== $order['waiter_id']) {
                    throw new Exception('Solo se pueden unir pedidos del mismo mesero');
                }
                
                // Validate all orders are from the same day
                $currentOrderDate = date('Y-m-d', strtotime($order['created_at']));
                if ($orderDate === null) {
                    $orderDate = $currentOrderDate;
                } elseif ($orderDate !== $currentOrderDate) {
                    throw new Exception('Solo se pueden unir pedidos del mismo día');
                }
                
                $orders[] = $order;
                $totalSubtotal += $order['total'];
            }
            
            if (empty($orders)) {
                throw new Exception('No se encontraron órdenes válidas');
            }
            
            // Calculate totals with proper rounding
            // Prices already include 16% IVA, so we need to separate it
            $totalWithTax = $totalSubtotal;
            $subtotal = round($totalWithTax / 1.16, 2); // Remove 16% IVA to get subtotal
            $tax = round($totalWithTax - $subtotal, 2); // Calculate the IVA amount
            $total = $totalWithTax; // Total remains the same
            
            // Validate data before insertion
            if ($subtotal <= 0) {
                throw new Exception('El subtotal debe ser mayor a cero');
            }
            
            if (!in_array($paymentMethod, ['efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar'])) {
                throw new Exception('Método de pago inválido');
            }
            
            // Create ticket for the first order (as main order)
            $mainOrder = $orders[0];
            $ticketData = [
                'order_id' => intval($mainOrder['id']),
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => intval($cashierId),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod
            ];
            
            // Log ticket creation attempt for debugging
            error_log("Multiple orders ticket creation attempt: " . json_encode($ticketData));
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket en la base de datos');
            }
            
            // Update all order statuses to delivered and customer stats
            $customerModel = new Customer();
            foreach ($orders as $order) {
                $orderModel->updateOrderStatus($order['id'], ORDER_DELIVERED);
                
                // Update customer statistics if order has a customer
                if ($order['customer_id']) {
                    $customerModel->updateStats($order['customer_id'], $order['total']);
                }
            }
            
            // Deduct inventory for all orders
            $this->deductInventoryForMultipleOrders($ticketId, $orderIds, $cashierId);
            
            // Free the table (set to available) since the ticket has been generated
            $tableModel = new Table();
            $tableModel->updateTableStatus($tableId, TABLE_AVAILABLE);
            
            $this->db->commit();
            error_log("Multiple orders ticket created successfully with ID: $ticketId");
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Multiple orders ticket creation failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getTicketWithDetails($ticketId) {
        $query = "SELECT t.*, o.table_id, o.waiter_id, o.notes as order_notes,
                         tb.number as table_number,
                         w.employee_code,
                         u_waiter.name as waiter_name,
                         u_cashier.name as cashier_name
                  FROM {$this->table} t
                  JOIN orders o ON t.order_id = o.id
                  JOIN tables tb ON o.table_id = tb.id
                  JOIN waiters w ON o.waiter_id = w.id
                  JOIN users u_waiter ON w.user_id = u_waiter.id
                  JOIN users u_cashier ON t.cashier_id = u_cashier.id
                  WHERE t.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        
        $ticket = $stmt->fetch();
        
        if ($ticket) {
            // Get all orders for this table that were delivered at the same time as this ticket
            $orderModel = new Order();
            
            // Get order items from the specific order linked to this ticket
            // For tickets created from multiple orders, this will show the main order's items
            // Note: This is a simplified approach that shows items from the primary order only
            $itemsQuery = "SELECT oi.*, d.name as dish_name, d.category, o.id as order_id
                          FROM order_items oi
                          JOIN dishes d ON oi.dish_id = d.id
                          JOIN orders o ON oi.order_id = o.id
                          WHERE o.id = ?
                          ORDER BY oi.created_at ASC";
            
            $stmt = $this->db->prepare($itemsQuery);
            $stmt->execute([$ticket['order_id']]);
            
            $ticket['items'] = $stmt->fetchAll();
            
            // Also get order details for reference
            $ticket['order_details'] = $orderModel->getOrderItems($ticket['order_id']);
        }
        
        return $ticket;
    }
    
    public function getTicketsByDate($date = null, $cashierId = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT t.*, o.table_id, tb.number as table_number,
                         u.name as cashier_name
                  FROM {$this->table} t
                  JOIN orders o ON t.order_id = o.id
                  JOIN tables tb ON o.table_id = tb.id
                  JOIN users u ON t.cashier_id = u.id
                  WHERE DATE(t.created_at) = ?";
        
        $params = [$date];
        
        if ($cashierId) {
            $query .= " AND t.cashier_id = ?";
            $params[] = $cashierId;
        }
        
        $query .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getDailySalesReport($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(subtotal) as total_subtotal,
                    SUM(tax) as total_tax,
                    SUM(total) as total_amount,
                    payment_method,
                    COUNT(*) as method_count
                  FROM {$this->table}
                  WHERE DATE(created_at) = ?
                  GROUP BY payment_method";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$date]);
        
        $results = $stmt->fetchAll();
        
        // Get overall totals
        $totalQuery = "SELECT 
                        COUNT(*) as total_tickets,
                        SUM(subtotal) as total_subtotal,
                        SUM(tax) as total_tax,
                        SUM(total) as total_amount
                       FROM {$this->table}
                       WHERE DATE(created_at) = ?";
        
        $stmt = $this->db->prepare($totalQuery);
        $stmt->execute([$date]);
        $totals = $stmt->fetch();
        
        return [
            'by_payment_method' => $results,
            'totals' => $totals
        ];
    }
    
    public function getSalesReportData($startDate, $endDate) {
        $query = "SELECT 
                    DATE(t.created_at) as date,
                    COUNT(*) as total_tickets,
                    SUM(t.subtotal) as total_subtotal,
                    SUM(t.tax) as total_tax,
                    SUM(t.total) as total_amount,
                    t.payment_method,
                    COUNT(*) as method_count
                  FROM {$this->table} t
                  WHERE DATE(t.created_at) BETWEEN ? AND ?
                  GROUP BY DATE(t.created_at), t.payment_method
                  ORDER BY DATE(t.created_at) DESC, t.payment_method";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
    
    // ============= INCOME REPORTING METHODS =============
    
    public function getTotalIncome($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(subtotal) as total_subtotal,
                    SUM(tax) as total_tax,
                    SUM(total) as total_income
                  FROM {$this->table} 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                    AND payment_method != 'pendiente_por_cobrar'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        
        return $stmt->fetch() ?: [
            'total_tickets' => 0,
            'total_subtotal' => 0,
            'total_tax' => 0,
            'total_income' => 0
        ];
    }
    
    public function getIncomeByDate($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as tickets_count,
                    SUM(subtotal) as subtotal,
                    SUM(tax) as tax,
                    SUM(total) as total_income
                  FROM {$this->table} 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                    AND payment_method != 'pendiente_por_cobrar'
                  GROUP BY DATE(created_at)
                  ORDER BY DATE(created_at) ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        
        return $stmt->fetchAll();
    }
    
    public function getIncomeByPaymentMethod($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                    payment_method,
                    COUNT(*) as tickets_count,
                    SUM(total) as total_income
                  FROM {$this->table} 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                  GROUP BY payment_method
                  ORDER BY total_income DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        
        return $stmt->fetchAll();
    }
    
    public function getIncomeVsExpensesData($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        // Get income by date
        $incomeData = $this->getIncomeByDate($dateFrom, $dateTo);
        
        // Get expenses by date
        $expenseModel = new Expense();
        $expenseQuery = "SELECT 
                            DATE(expense_date) as date,
                            SUM(amount) as total_expenses
                         FROM expenses 
                         WHERE DATE(expense_date) BETWEEN ? AND ?
                         GROUP BY DATE(expense_date)
                         ORDER BY DATE(expense_date) ASC";
        
        $stmt = $this->db->prepare($expenseQuery);
        $stmt->execute([$dateFrom, $dateTo]);
        $expenseData = $stmt->fetchAll();
        
        // Get withdrawals by date
        $withdrawalModel = new CashWithdrawal();
        $withdrawalQuery = "SELECT 
                               DATE(withdrawal_date) as date,
                               SUM(amount) as total_withdrawals
                            FROM cash_withdrawals 
                            WHERE DATE(withdrawal_date) BETWEEN ? AND ?
                            GROUP BY DATE(withdrawal_date)
                            ORDER BY DATE(withdrawal_date) ASC";
        
        $stmt = $this->db->prepare($withdrawalQuery);
        $stmt->execute([$dateFrom, $dateTo]);
        $withdrawalData = $stmt->fetchAll();
        
        // Combine income, expense and withdrawal data by date
        $combinedData = [];
        $expenseByDate = [];
        $withdrawalByDate = [];
        
        foreach ($expenseData as $expense) {
            $expenseByDate[$expense['date']] = (float)$expense['total_expenses'];
        }
        
        foreach ($withdrawalData as $withdrawal) {
            $withdrawalByDate[$withdrawal['date']] = (float)$withdrawal['total_withdrawals'];
        }
        
        foreach ($incomeData as $income) {
            $date = $income['date'];
            $totalExpenses = ($expenseByDate[$date] ?? 0) + ($withdrawalByDate[$date] ?? 0);
            $combinedData[] = [
                'date' => $date,
                'income' => (float)$income['total_income'],
                'expenses' => $expenseByDate[$date] ?? 0,
                'withdrawals' => $withdrawalByDate[$date] ?? 0,
                'total_expenses' => $totalExpenses,
                'net_profit' => (float)$income['total_income'] - $totalExpenses
            ];
        }
        
        return $combinedData;
    }
    
    public function getPendingPayments() {
        $query = "SELECT t.*, 
                         tn.number as table_number,
                         u.name as cashier_name,
                         u_waiter.name as waiter_name,
                         w.employee_code
                  FROM tickets t
                  LEFT JOIN orders o ON t.order_id = o.id
                  LEFT JOIN tables tn ON o.table_id = tn.id
                  LEFT JOIN users u ON t.cashier_id = u.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u_waiter ON w.user_id = u_waiter.id
                  WHERE t.payment_method = 'pendiente_por_cobrar'
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updatePaymentMethod($ticketId, $paymentMethod) {
        $validMethods = ['efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar'];
        if (!in_array($paymentMethod, $validMethods)) {
            return false;
        }
        
        $query = "UPDATE tickets SET payment_method = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$paymentMethod, $ticketId]);
    }
    
    public function createExpiredOrderTicket($orderId, $cashierId, $paymentMethod = 'efectivo') {
        try {
            $this->db->beginTransaction();
            
            $orderModel = new Order();
            $tableModel = new Table();
            
            // Get order details
            $order = $orderModel->find($orderId);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }
            
            if ($order['status'] !== ORDER_READY) {
                throw new Exception('El pedido debe estar en estado "Listo" para generar el ticket');
            }
            
            // Check if order already has a ticket
            $existingTicket = $this->findBy('order_id', $orderId);
            if ($existingTicket) {
                throw new Exception('Este pedido ya tiene un ticket generado');
            }
            
            // For expired orders, we allow ticket generation with today's date
            // This ensures the income is recorded for today's date in reports
            
            // Calculate totals with proper rounding
            // Prices already include 16% IVA, so we need to separate it
            $totalWithTax = floatval($order['total']);
            $subtotal = round($totalWithTax / 1.16, 2); // Remove 16% IVA to get subtotal
            $tax = round($totalWithTax - $subtotal, 2); // Calculate the IVA amount
            $total = $totalWithTax; // Total remains the same
            
            // Validate data before insertion
            if ($subtotal <= 0) {
                throw new Exception('El subtotal debe ser mayor a cero');
            }
            
            if (!in_array($paymentMethod, ['efectivo', 'tarjeta', 'transferencia', 'intercambio', 'pendiente_por_cobrar'])) {
                throw new Exception('Método de pago inválido');
            }
            
            // Create ticket with today's date for proper reporting
            $ticketData = [
                'order_id' => intval($orderId),
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => intval($cashierId),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'created_at' => date('Y-m-d H:i:s') // Force today's date for reporting
            ];
            
            // Log ticket creation attempt for debugging
            error_log("Expired order ticket creation attempt: " . json_encode($ticketData));
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket en la base de datos');
            }
            
            // Update order status and customer stats
            $orderModel->updateOrderStatusAndCustomerStats($orderId, ORDER_DELIVERED);
            
            // Update customer statistics if order has a customer
            if ($order['customer_id']) {
                $customerModel = new Customer();
                $customerModel->updateStats($order['customer_id'], $order['total']);
            }
            
            // Free the table (set to available) since the ticket has been generated
            if ($order['table_id']) {
                $tableModel->updateTableStatus($order['table_id'], TABLE_AVAILABLE);
            }
            
            $this->db->commit();
            error_log("Expired order ticket created successfully with ID: $ticketId");
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Expired order ticket creation failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getPaymentMethodStats($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                     payment_method,
                     COUNT(*) as ticket_count,
                     SUM(total) as total_amount
                  FROM tickets 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                  GROUP BY payment_method
                  ORDER BY total_amount DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getIntercambioTotal($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                     COUNT(*) as count,
                     COALESCE(SUM(total), 0) as total_amount
                  FROM tickets 
                  WHERE payment_method = 'intercambio'
                  AND DATE(created_at) BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetch();
    }
    
    public function getTicketsByPaymentMethod($paymentMethod, $dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT t.*, 
                         o.table_id,
                         tn.number as table_number,
                         u.name as cashier_name,
                         u_waiter.name as waiter_name,
                         w.employee_code
                  FROM tickets t
                  LEFT JOIN orders o ON t.order_id = o.id
                  LEFT JOIN tables tn ON o.table_id = tn.id
                  LEFT JOIN users u ON t.cashier_id = u.id
                  LEFT JOIN waiters w ON o.waiter_id = w.id
                  LEFT JOIN users u_waiter ON w.user_id = u_waiter.id
                  WHERE t.payment_method = ?
                    AND DATE(t.created_at) BETWEEN ? AND ?
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$paymentMethod, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getPendingPaymentTotal($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $query = "SELECT 
                     COUNT(*) as count,
                     COALESCE(SUM(total), 0) as total_amount
                  FROM tickets 
                  WHERE payment_method = 'pendiente_por_cobrar'
                  AND DATE(created_at) BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetch();
    }
    
    // ============= MÉTODOS DE INTEGRACIÓN CON INVENTARIO =============
    
    private function deductInventoryForTicket($ticketId, $orderId, $userId) {
        // Verificar si el inventario y la deducción automática están habilitados
        $systemSettingsModel = new SystemSettings();
        
        if (!$systemSettingsModel->isInventoryEnabled() || !$systemSettingsModel->isAutoDeductInventoryEnabled()) {
            return; // No hacer nada si no está habilitado
        }
        
        try {
            // Obtener los items del pedido
            $orderItemModel = new OrderItem();
            $orderItems = $orderItemModel->getItemsByOrder($orderId);
            
            $dishIngredientModel = new DishIngredient();
            
            foreach ($orderItems as $item) {
                // Descontar ingredientes por cada platillo vendido
                $dishIngredientModel->deductIngredientsForDish(
                    $item['dish_id'], 
                    $item['quantity'], 
                    $userId, 
                    $ticketId
                );
            }
            
        } catch (Exception $e) {
            // Log the error but don't fail the ticket creation
            error_log("Error deducting inventory for ticket {$ticketId}: " . $e->getMessage());
            // En producción podrías querer mostrar una advertencia al usuario
        }
    }
    
    private function deductInventoryForMultipleOrders($ticketId, $orderIds, $userId) {
        // Verificar si el inventario y la deducción automática están habilitados
        $systemSettingsModel = new SystemSettings();
        
        if (!$systemSettingsModel->isInventoryEnabled() || !$systemSettingsModel->isAutoDeductInventoryEnabled()) {
            return; // No hacer nada si no está habilitado
        }
        
        try {
            $orderItemModel = new OrderItem();
            $dishIngredientModel = new DishIngredient();
            
            foreach ($orderIds as $orderId) {
                // Obtener los items de cada pedido
                $orderItems = $orderItemModel->getItemsByOrder($orderId);
                
                foreach ($orderItems as $item) {
                    // Descontar ingredientes por cada platillo vendido
                    $dishIngredientModel->deductIngredientsForDish(
                        $item['dish_id'], 
                        $item['quantity'], 
                        $userId, 
                        $ticketId
                    );
                }
            }
            
        } catch (Exception $e) {
            // Log the error but don't fail the ticket creation
            error_log("Error deducting inventory for multiple orders ticket {$ticketId}: " . $e->getMessage());
        }
    }
}
?>