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
            
            // Calculate totals
            $subtotal = $order['total'];
            $tax = $subtotal * 0.16; // 16% IVA
            $total = $subtotal + $tax;
            
            // Create ticket
            $ticketData = [
                'order_id' => $orderId,
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => $cashierId,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod
            ];
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket');
            }
            
            // Update order status
            $orderModel->updateOrderStatus($orderId, ORDER_DELIVERED);
            
            // Free the table (set to available) since the ticket has been generated
            $tableModel = new Table();
            $tableModel->updateTableStatus($order['table_id'], TABLE_AVAILABLE);
            
            $this->db->commit();
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function createTicketFromMultipleOrders($orderIds, $cashierId, $paymentMethod = 'efectivo') {
        try {
            $this->db->beginTransaction();
            
            // Get order details and validate they're all from the same table
            $orderModel = new Order();
            $orders = [];
            $tableId = null;
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
                
                $orders[] = $order;
                $totalSubtotal += $order['total'];
            }
            
            if (empty($orders)) {
                throw new Exception('No se encontraron órdenes válidas');
            }
            
            // Calculate totals
            $tax = $totalSubtotal * 0.16; // 16% IVA
            $total = $totalSubtotal + $tax;
            
            // Create ticket for the first order (as main order)
            $mainOrder = $orders[0];
            $ticketData = [
                'order_id' => $mainOrder['id'],
                'ticket_number' => $this->generateTicketNumber(),
                'cashier_id' => $cashierId,
                'subtotal' => $totalSubtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod
            ];
            
            $ticketId = $this->create($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Error al crear el ticket');
            }
            
            // Update all order statuses to delivered
            foreach ($orders as $order) {
                $orderModel->updateOrderStatus($order['id'], ORDER_DELIVERED);
            }
            
            // Free the table (set to available) since the ticket has been generated
            $tableModel = new Table();
            $tableModel->updateTableStatus($tableId, TABLE_AVAILABLE);
            
            $this->db->commit();
            return $ticketId;
            
        } catch (Exception $e) {
            $this->db->rollback();
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
            
            // Get all order items from orders that were processed together
            // This handles both single orders and multiple orders from the same table
            $itemsQuery = "SELECT oi.*, d.name as dish_name, d.category, o.id as order_id
                          FROM order_items oi
                          JOIN dishes d ON oi.dish_id = d.id
                          JOIN orders o ON oi.order_id = o.id
                          WHERE o.table_id = ? 
                          AND o.status = ?
                          AND DATE(o.updated_at) = DATE(?)
                          ORDER BY o.id ASC, oi.created_at ASC";
            
            $stmt = $this->db->prepare($itemsQuery);
            $stmt->execute([$ticket['table_id'], ORDER_DELIVERED, $ticket['created_at']]);
            
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
}
?>