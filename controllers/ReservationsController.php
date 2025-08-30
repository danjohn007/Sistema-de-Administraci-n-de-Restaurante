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
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('reservations/create', [
                'tables' => $tables
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
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('reservations/edit', [
                'reservation' => $reservation,
                'tables' => $tables
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
                    'table_id' => $_POST['table_id'],
                    'reservation_datetime' => $_POST['reservation_datetime'],
                    'party_size' => $_POST['party_size'],
                    'notes' => $_POST['notes'] ?? null,
                    'status' => 'pendiente'
                ];
                
                $customerData = [
                    'name' => $_POST['customer_name'],
                    'phone' => $_POST['customer_phone'],
                    'birthday' => !empty($_POST['customer_birthday']) ? $_POST['customer_birthday'] : null
                ];
                
                // Check table availability
                if (!$this->reservationModel->checkTableAvailability($_POST['table_id'], $_POST['reservation_datetime'])) {
                    throw new Exception('La mesa no está disponible en el horario seleccionado');
                }
                
                $reservationId = $this->reservationModel->createReservationWithCustomer($reservationData, $customerData);
                $this->redirect('reservations/show/' . $reservationId, 'success', 'Reservación creada correctamente');
                
            } catch (Exception $e) {
                $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
                
                $this->view('reservations/create', [
                    'error' => 'Error al crear la reservación: ' . $e->getMessage(),
                    'old' => $_POST,
                    'tables' => $tables
                ]);
            }
        } else {
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('reservations/create', [
                'errors' => $errors,
                'old' => $_POST,
                'tables' => $tables
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateReservationInput($_POST);
        
        if (empty($errors)) {
            try {
                $updateData = [
                    'table_id' => $_POST['table_id'],
                    'reservation_datetime' => $_POST['reservation_datetime'],
                    'party_size' => $_POST['party_size'],
                    'notes' => $_POST['notes'] ?? null
                ];
                
                // Check table availability (excluding current reservation)
                if (!$this->reservationModel->checkTableAvailability($_POST['table_id'], $_POST['reservation_datetime'], $id)) {
                    throw new Exception('La mesa no está disponible en el horario seleccionado');
                }
                
                $this->reservationModel->update($id, $updateData);
                $this->redirect('reservations/show/' . $id, 'success', 'Reservación actualizada correctamente');
                
            } catch (Exception $e) {
                $reservation = $this->reservationModel->find($id);
                $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
                
                $this->view('reservations/edit', [
                    'error' => 'Error al actualizar la reservación: ' . $e->getMessage(),
                    'old' => $_POST,
                    'reservation' => $reservation,
                    'tables' => $tables
                ]);
            }
        } else {
            $reservation = $this->reservationModel->find($id);
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->view('reservations/edit', [
                'errors' => $errors,
                'old' => $_POST,
                'reservation' => $reservation,
                'tables' => $tables
            ]);
        }
    }
    
    private function validateReservationInput($data) {
        $errors = $this->validateInput($data, [
            'customer_name' => ['required' => true, 'max' => 255],
            'customer_phone' => ['required' => true, 'max' => 20],
            'table_id' => ['required' => true],
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
        
        return $errors;
    }
}
?>