<?php
class Reservation extends BaseModel {
    protected $table = 'reservations';
    
    public function getReservationsWithTables($filters = [], $orderBy = 'reservation_datetime ASC') {
        $query = "SELECT r.*, t.number as table_number, t.capacity as table_capacity 
                  FROM reservations r 
                  JOIN tables t ON r.table_id = t.id 
                  WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $query .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date'])) {
            $query .= " AND DATE(r.reservation_datetime) = ?";
            $params[] = $filters['date'];
        }
        
        if (isset($filters['table_id'])) {
            $query .= " AND r.table_id = ?";
            $params[] = $filters['table_id'];
        }
        
        $query .= " ORDER BY " . $orderBy;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getTodaysReservations() {
        return $this->getReservationsWithTables(['date' => date('Y-m-d')]);
    }
    
    public function getFutureReservations() {
        $query = "SELECT r.*, t.number as table_number, t.capacity as table_capacity 
                  FROM reservations r 
                  JOIN tables t ON r.table_id = t.id 
                  WHERE r.reservation_datetime > NOW() 
                  ORDER BY r.reservation_datetime ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function checkTableAvailability($tableId, $datetime, $excludeReservationId = null) {
        $query = "SELECT COUNT(*) as count 
                  FROM reservations 
                  WHERE table_id = ? 
                  AND status IN ('pendiente', 'confirmada') 
                  AND ABS(TIMESTAMPDIFF(MINUTE, reservation_datetime, ?)) < 120"; // 2 hour buffer
        
        $params = [$tableId, $datetime];
        
        if ($excludeReservationId) {
            $query .= " AND id != ?";
            $params[] = $excludeReservationId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['count'] == 0;
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
            
            // Create reservation with customer data directly stored
            $reservationId = $this->create($reservationData);
            
            $this->db->commit();
            return $reservationId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}