<?php
class ReservationsController extends BaseController {
    private $reservationModel;
    private $tableModel;
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->reservationModel = new Reservation();
        $this->tableModel = new Table();
        $this->customerModel = new Customer();
    }
    
    public function index() {
        $filter = $_GET['filter'] ?? 'today';
        
        switch ($filter) {
            case 'today':
                $reservations = $this->reservationModel->getTodaysReservations();
                break;
            case 'future':
                $reservations = $this->reservationModel->getFutureReservations();
                break;
            case 'all':
                $reservations = $this->reservationModel->getReservationsWithTables();
                break;
            default:
                $reservations = $this->reservationModel->getTodaysReservations();
        }
        
        $this->view('reservations/index', [
            'reservations' => $reservations,
            'filter' => $filter
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            // Show only available tables for reservation
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            // Get waiters for assignment (available to all user roles)
            $waiterModel = new Waiter();
            $waiters = $waiterModel->getWaitersWithUsers();
            
            $this->view('reservations/create', [
                'tables' => $tables,
                'waiters' => $waiters
            ]);
        }
    }
    
    public function show($id) {
        $reservation = $this->reservationModel->find($id);
        if (!$reservation) {
            $this->redirect('reservations', 'error', 'Reservación no encontrada');
            return;
        }
        
        $reservationWithTable = $this->reservationModel->getReservationsWithTables(['id' => $id]);
        $reservation = $reservationWithTable[0] ?? $reservation;
        
        $this->view('reservations/view', [
            'reservation' => $reservation
        ]);
    }
    
    public function edit($id) {
        $reservation = $this->reservationModel->find($id);
        if (!$reservation) {
            $this->redirect('reservations', 'error', 'Reservación no encontrada');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            // Show available tables plus tables already assigned to this reservation
            $tables = $this->tableModel->getAvailableTablesForReservationEdit($id);
            $reservationTables = $this->reservationModel->getReservationTables($id);
            
            // Get waiters for assignment
            $waiterModel = new Waiter();
            $waiters = $waiterModel->getWaitersWithUsers();
            
            // Format customer birthday for display if it exists
            if (isset($reservation['customer_birthday']) && !empty($reservation['customer_birthday'])) {
                // Check if it's already in DD/MM format
                if (!preg_match('/^\d{2}\/\d{2}$/', $reservation['customer_birthday'])) {
                    // If it's a date, try to convert it to DD/MM
                    $birthday = $reservation['customer_birthday'];
                    if (strtotime($birthday)) {
                        $reservation['customer_birthday'] = date('d/m', strtotime($birthday));
                    }
                }
            }
            
            $this->view('reservations/edit', [
                'reservation' => $reservation,
                'tables' => $tables,
                'reservationTables' => $reservationTables,
                'waiters' => $waiters
            ]);
        }
    }
    
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservations', 'error', 'Método no permitido');
            return;
        }
        
        $reservation = $this->reservationModel->find($id);
        if (!$reservation) {
            $this->redirect('reservations', 'error', 'Reservación no encontrada');
            return;
        }
        
        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['pendiente', 'confirmada', 'cancelada', 'completada'];
        
        if (!in_array($newStatus, $validStatuses)) {
            $this->redirect('reservations', 'error', 'Estado inválido');
            return;
        }
        
        try {
            $this->reservationModel->update($id, ['status' => $newStatus]);
            $this->redirect('reservations/show/' . $id, 'success', 'Estado actualizado correctamente');
        } catch (Exception $e) {
            $this->redirect('reservations', 'error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }
    
    public function delete($id) {
        $user = $this->getCurrentUser();
        if ($user['role'] !== ROLE_ADMIN) {
            $this->redirect('reservations', 'error', 'No tienes permisos para eliminar reservaciones');
            return;
        }
        
        try {
            $this->reservationModel->delete($id);
            $this->redirect('reservations', 'success', 'Reservación eliminada correctamente');
        } catch (Exception $e) {
            $this->redirect('reservations', 'error', 'Error al eliminar la reservación: ' . $e->getMessage());
        }
    }
    
    private function processCreate() {
        $errors = $this->validateReservationInput($_POST);
        
        if (empty($errors)) {
            try {
                $reservationData = [
                    'reservation_datetime' => $_POST['reservation_datetime'],
                    'party_size' => $_POST['party_size'],
                    'notes' => $_POST['notes'] ?? null,
                    'status' => 'pendiente'
                ];
                
                // Handle waiter assignment
                if (!empty($_POST['waiter_id'])) {
                    $reservationData['waiter_id'] = $_POST['waiter_id'];
                }
                
                // Handle table selection (can be multiple or none)
                $tableIds = [];
                if (!empty($_POST['table_ids']) && is_array($_POST['table_ids'])) {
                    $tableIds = array_filter($_POST['table_ids'], function($id) {
                        return !empty($id) && is_numeric($id);
                    });
                } elseif (!empty($_POST['table_id'])) {
                    // Support single table selection for backwards compatibility
                    $tableIds = [$_POST['table_id']];
                }
                
                $reservationData['table_ids'] = $tableIds;
                
                $customerData = [
                    'name' => $_POST['customer_name'],
                    'phone' => $_POST['customer_phone'],
                    'birthday' => !empty($_POST['customer_birthday']) ? $_POST['customer_birthday'] : null
                ];
                
                // Check table availability if tables are specified
                if (!empty($tableIds)) {
                    if (!$this->reservationModel->checkTableAvailability($tableIds, $_POST['reservation_datetime'])) {
                        throw new Exception('Una o más mesas no están disponibles en el horario seleccionado');
                    }
                }
                
                $reservationId = $this->reservationModel->createReservationWithCustomer($reservationData, $customerData);
                $this->redirect('reservations/show/' . $reservationId, 'success', 'Reservación creada correctamente');
                
            } catch (Exception $e) {
                // Show only available tables for reservation
                $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
                $waiterModel = new Waiter();
                $waiters = $waiterModel->getWaitersWithUsers();
                
                $this->view('reservations/create', [
                    'error' => 'Error al crear la reservación: ' . $e->getMessage(),
                    'old' => $_POST,
                    'tables' => $tables,
                    'waiters' => $waiters
                ]);
            }
        } else {
            // Show only available tables for reservation
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            $waiterModel = new Waiter();
            $waiters = $waiterModel->getWaitersWithUsers();
            
            $this->view('reservations/create', [
                'errors' => $errors,
                'old' => $_POST,
                'tables' => $tables,
                'waiters' => $waiters
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateReservationInput($_POST);
        
        if (empty($errors)) {
            try {
                $updateData = [
                    'reservation_datetime' => $_POST['reservation_datetime'],
                    'party_size' => $_POST['party_size'],
                    'notes' => $_POST['notes'] ?? null
                ];
                
                // Handle waiter assignment
                if (!empty($_POST['waiter_id'])) {
                    $updateData['waiter_id'] = $_POST['waiter_id'];
                } else {
                    $updateData['waiter_id'] = null;
                }
                
                // Handle table selection (can be multiple or none)
                $tableIds = [];
                if (!empty($_POST['table_ids']) && is_array($_POST['table_ids'])) {
                    $tableIds = array_filter($_POST['table_ids'], function($id) {
                        return !empty($id) && is_numeric($id);
                    });
                } elseif (!empty($_POST['table_id'])) {
                    // Support single table selection for backwards compatibility
                    $tableIds = [$_POST['table_id']];
                }
                
                // Check table availability if tables are specified (excluding current reservation)
                if (!empty($tableIds)) {
                    if (!$this->reservationModel->checkTableAvailability($tableIds, $_POST['reservation_datetime'], $id)) {
                        throw new Exception('Una o más mesas no están disponibles en el horario seleccionado');
                    }
                }
                
                // Update reservation and tables
                $this->reservationModel->updateReservationWithTables($id, $updateData, $tableIds);
                $this->redirect('reservations/show/' . $id, 'success', 'Reservación actualizada correctamente');
                
            } catch (Exception $e) {
                $reservation = $this->reservationModel->find($id);
                $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
                $reservationTables = $this->reservationModel->getReservationTables($id);
                $waiterModel = new Waiter();
                $waiters = $waiterModel->getWaitersWithUsers();
                
                $this->view('reservations/edit', [
                    'error' => 'Error al actualizar la reservación: ' . $e->getMessage(),
                    'old' => $_POST,
                    'reservation' => $reservation,
                    'tables' => $tables,
                    'reservationTables' => $reservationTables,
                    'waiters' => $waiters
                ]);
            }
        } else {
            $reservation = $this->reservationModel->find($id);
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            $reservationTables = $this->reservationModel->getReservationTables($id);
            $waiterModel = new Waiter();
            $waiters = $waiterModel->getWaitersWithUsers();
            
            $this->view('reservations/edit', [
                'errors' => $errors,
                'old' => $_POST,
                'reservation' => $reservation,
                'tables' => $tables,
                'reservationTables' => $reservationTables,
                'waiters' => $waiters
            ]);
        }
    }
    
    private function validateReservationInput($data) {
        $errors = $this->validateInput($data, [
            'customer_name' => ['required' => true, 'max' => 255],
            'customer_phone' => ['required' => true, 'max' => 20],
            'reservation_datetime' => ['required' => true],
            'party_size' => ['required' => true, 'min' => 1, 'max' => 20]
        ]);
        
        // Validate reservation datetime
        if (isset($data['reservation_datetime']) && !empty($data['reservation_datetime'])) {
            $reservationTime = strtotime($data['reservation_datetime']);
            $now = time();
            
            // Minimum 30 minutes advance notice
            $minTime = $now + (30 * 60);
            
            // Maximum 30 days advance
            $maxTime = $now + (30 * 24 * 60 * 60);
            
            if ($reservationTime <= $minTime) {
                $errors['reservation_datetime'] = 'La fecha y hora de reservación debe ser al menos 30 minutos en adelante';
            } elseif ($reservationTime > $maxTime) {
                $errors['reservation_datetime'] = 'La fecha y hora de reservación no puede ser más de 30 días en adelante';
            }
        }
        
        // Validate waiter assignment (optional but if provided, must be valid)
        if (!empty($data['waiter_id'])) {
            $waiterModel = new Waiter();
            $waiter = $waiterModel->find($data['waiter_id']);
            if (!$waiter || !$waiter['active']) {
                $errors['waiter_id'] = 'El mesero seleccionado no es válido';
            }
        }
        
        return $errors;
    }
    
    public function getAvailableTablesByDate() {
        // This method handles AJAX requests to get available tables for a specific date
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['datetime'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datetime parameter required']);
            return;
        }
        
        $datetime = $data['datetime'];
        $excludeReservationId = $data['exclude_reservation_id'] ?? null;
        
        try {
            // Get all active tables
            $allTables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            // Filter available tables based on the datetime
            $availableTables = [];
            foreach ($allTables as $table) {
                if ($this->reservationModel->checkTableAvailability([$table['id']], $datetime, $excludeReservationId)) {
                    $availableTables[] = $table;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'tables' => $availableTables
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error fetching available tables: ' . $e->getMessage()]);
        }
    }
}
?>