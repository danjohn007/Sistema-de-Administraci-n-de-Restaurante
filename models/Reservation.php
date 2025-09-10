<?php
class Reservation extends BaseModel {
    protected $table = 'reservations';
    
    public function getReservationsWithTables($filters = [], $orderBy = 'reservation_datetime ASC') {
        $query = "SELECT r.*, 
                         w.employee_code as waiter_code,
                         u.name as waiter_name,
                         GROUP_CONCAT(DISTINCT t.number ORDER BY t.number ASC) as table_numbers,
                         GROUP_CONCAT(DISTINCT CONCAT('Mesa ', t.number, ' (', t.capacity, ')') ORDER BY t.number ASC SEPARATOR ', ') as table_details,
                         SUM(t.capacity) as total_capacity
                  FROM reservations r 
                  LEFT JOIN reservation_tables rt ON r.id = rt.reservation_id
                  LEFT JOIN tables t ON rt.table_id = t.id
                  LEFT JOIN waiters w ON r.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['id'])) {
            $query .= " AND r.id = ?";
            $params[] = $filters['id'];
        }
        
        if (isset($filters['status'])) {
            $query .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date'])) {
            $query .= " AND DATE(r.reservation_datetime) = ?";
            $params[] = $filters['date'];
        }
        
        if (isset($filters['table_id'])) {
            $query .= " AND rt.table_id = ?";
            $params[] = $filters['table_id'];
        }
        
        $query .= " GROUP BY r.id ORDER BY " . $orderBy;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getTodaysReservations() {
        return $this->getReservationsWithTables(['date' => date('Y-m-d')]);
    }
    
    public function getFutureReservations() {
        $query = "SELECT r.*, 
                         w.employee_code as waiter_code,
                         u.name as waiter_name,
                         GROUP_CONCAT(DISTINCT t.number ORDER BY t.number ASC) as table_numbers,
                         GROUP_CONCAT(DISTINCT CONCAT('Mesa ', t.number, ' (', t.capacity, ')') ORDER BY t.number ASC SEPARATOR ', ') as table_details,
                         SUM(t.capacity) as total_capacity
                  FROM reservations r 
                  LEFT JOIN reservation_tables rt ON r.id = rt.reservation_id
                  LEFT JOIN tables t ON rt.table_id = t.id
                  LEFT JOIN waiters w ON r.waiter_id = w.id
                  LEFT JOIN users u ON w.user_id = u.id
                  WHERE r.reservation_datetime > NOW() 
                  GROUP BY r.id 
                  ORDER BY r.reservation_datetime ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function checkTableAvailability($tableIds, $datetime, $excludeReservationId = null) {
        // If it's a single table ID, convert to array
        if (!is_array($tableIds)) {
            $tableIds = [$tableIds];
        }
        
        // If no tables specified, return true (system will auto-assign)
        if (empty($tableIds)) {
            return true;
        }
        
        $placeholders = str_repeat('?,', count($tableIds) - 1) . '?';
        
        $query = "SELECT COUNT(*) as count 
                  FROM reservations r
                  JOIN reservation_tables rt ON r.id = rt.reservation_id
                  WHERE rt.table_id IN ($placeholders)
                  AND r.status IN ('pendiente', 'confirmada') 
                  AND ABS(TIMESTAMPDIFF(MINUTE, r.reservation_datetime, ?)) < 120"; // 2 hour buffer
        
        $params = array_merge($tableIds, [$datetime]);
        
        if ($excludeReservationId) {
            $query .= " AND r.id != ?";
            $params[] = $excludeReservationId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }
    
    public function getTablesBlockedByReservations($dateTime = null) {
        if (!$dateTime) {
            $dateTime = date('Y-m-d H:i:s');
        }
        
        // Get reservations that should block tables (30 minutes before reservation time)
        $query = "SELECT rt.table_id, r.reservation_datetime, r.customer_name, r.id as reservation_id
                  FROM reservations r
                  JOIN reservation_tables rt ON r.id = rt.reservation_id
                  WHERE r.status IN ('pendiente', 'confirmada')
                  AND TIMESTAMPDIFF(MINUTE, ?, r.reservation_datetime) BETWEEN 0 AND 30";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateTime]);
        
        return $stmt->fetchAll();
    }
    
    public function isTableBlockedByReservation($tableId, $dateTime = null) {
        if (!$dateTime) {
            $dateTime = date('Y-m-d H:i:s');
        }
        
        $blockedTables = $this->getTablesBlockedByReservations($dateTime);
        
        foreach ($blockedTables as $blocked) {
            if ($blocked['table_id'] == $tableId) {
                return $blocked;
            }
        }
        
        return false;
    }
    
    public function forceUnblockTable($tableId, $userId, $reason) {
        // Only admin and cashier can force unblock
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (!$user || !in_array($user['role'], [ROLE_ADMIN, ROLE_CASHIER])) {
            throw new Exception('No tienes permisos para desbloquear mesas');
        }
        
        // Log the unblock action
        $query = "INSERT INTO table_unblock_log (table_id, unblocked_by, reason, created_at) 
                  VALUES (?, ?, ?, NOW())";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$tableId, $userId, $reason]);
            return true;
        } catch (Exception $e) {
            // If log table doesn't exist, continue anyway
            error_log("Could not log table unblock: " . $e->getMessage());
            return true;
        }
    }
    
    public function canUseTable($tableId, $userId) {
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Admin and cashier can always use tables
        if (in_array($user['role'], [ROLE_ADMIN, ROLE_CASHIER])) {
            return true;
        }
        
        // Check if table is blocked by reservation
        $blocked = $this->isTableBlockedByReservation($tableId);
        
        if ($blocked) {
            return false; // Waiters cannot use blocked tables
        }
        
        return true;
    }
    
    public function createReservationWithCustomer($reservationData, $customerData) {
        try {
            $this->db->beginTransaction();
            
            // Store customer information directly in reservation (original design)
            // The reservations table has customer_name, customer_phone, customer_birthday columns
            $reservationData['customer_name'] = $customerData['name'];
            $reservationData['customer_phone'] = $customerData['phone'];
            $reservationData['customer_birthday'] = $customerData['birthday'] ?? null;
            
            // Also create/update customer record for tracking purposes
            $customerModel = new Customer();
            $customer = $customerModel->findBy('phone', $customerData['phone']);
            
            if (!$customer) {
                $customerModel->create($customerData);
            } else {
                // Update customer info if provided
                if (isset($customerData['name']) && $customerData['name'] !== $customer['name']) {
                    $customerModel->update($customer['id'], ['name' => $customerData['name']]);
                }
                if (isset($customerData['birthday']) && $customerData['birthday'] !== $customer['birthday']) {
                    $customerModel->update($customer['id'], ['birthday' => $customerData['birthday']]);
                }
            }
            
            // Extract table IDs and waiter ID before creating reservation
            $tableIds = isset($reservationData['table_ids']) ? $reservationData['table_ids'] : [];
            $waiterId = isset($reservationData['waiter_id']) ? $reservationData['waiter_id'] : null;
            
            // Remove table_ids from reservation data as it's not a column in reservations table
            unset($reservationData['table_ids']);
            
            // Set waiter_id
            $reservationData['waiter_id'] = $waiterId;
            
            // Create reservation with customer data directly stored
            $reservationId = $this->create($reservationData);
            
            // Add tables to the reservation if specified
            if (!empty($tableIds)) {
                $this->addTablesToReservation($reservationId, $tableIds);
            }
            
            $this->db->commit();
            return $reservationId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function addTablesToReservation($reservationId, $tableIds) {
        if (!is_array($tableIds)) {
            $tableIds = [$tableIds];
        }
        
        // Remove existing table assignments for this reservation and unblock those tables
        $currentTables = $this->getReservationTables($reservationId);
        foreach ($currentTables as $table) {
            // Only unblock if it's not occupied by an order
            $tableModel = new Table();
            $currentOrder = $tableModel->getCurrentOrder($table['id']);
            if (!$currentOrder) {
                $tableModel->updateTableStatus($table['id'], TABLE_AVAILABLE);
            }
        }
        
        $stmt = $this->db->prepare("DELETE FROM reservation_tables WHERE reservation_id = ?");
        $stmt->execute([$reservationId]);
        
        // Add new table assignments and block the tables
        $tableModel = new Table();
        foreach ($tableIds as $tableId) {
            if (!empty($tableId)) {
                $stmt = $this->db->prepare("INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)");
                $stmt->execute([$reservationId, $tableId]);
                
                // Block the table - set to occupied status for reservation
                $tableModel->updateTableStatus($tableId, TABLE_OCCUPIED);
            }
        }
    }
    
    public function getReservationTables($reservationId) {
        $query = "SELECT t.* 
                  FROM tables t 
                  JOIN reservation_tables rt ON t.id = rt.table_id 
                  WHERE rt.reservation_id = ? 
                  ORDER BY t.number ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$reservationId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateReservationWithTables($reservationId, $reservationData, $tableIds = []) {
        try {
            $this->db->beginTransaction();
            
            // Update reservation data
            $this->update($reservationId, $reservationData);
            
            // Update table assignments if provided
            if (!empty($tableIds)) {
                $this->addTablesToReservation($reservationId, $tableIds);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}