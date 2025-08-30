<?php
class PublicController extends BaseController {
    private $dishModel;
    private $tableModel;
    private $orderModel;
    private $reservationModel;
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->dishModel = new Dish();
        $this->tableModel = new Table();
        $this->orderModel = new Order();
        $this->reservationModel = new Reservation();
        $this->customerModel = new Customer();
    }
    
    public function menu() {
        $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
        $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
        
        $this->viewPublic('public/menu', [
            'dishes' => $dishes,
            'tables' => $tables
        ]);
    }
    
    public function order() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPublicOrder();
        } else {
            $this->redirect('public/menu');
        }
    }
    
    private function processPublicOrder() {
        $errors = $this->validatePublicOrderInput($_POST);
        
        if (!empty($errors)) {
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'errors' => $errors,
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
            return;
        }
        
        // Process items
        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (isset($item['quantity']) && $item['quantity'] > 0) {
                    $dish = $this->dishModel->find($item['dish_id']);
                    if ($dish) {
                        $items[] = [
                            'dish_id' => $item['dish_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $dish['price'],
                            'notes' => $item['notes'] ?? null
                        ];
                    }
                }
            }
        }
        
        if (empty($items)) {
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'errors' => ['items' => 'Debe agregar al menos un platillo al pedido'],
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
            return;
        }
        
        // Create order data
        $orderData = [
            'table_id' => !empty($_POST['table_id']) ? $_POST['table_id'] : null,
            'waiter_id' => null, // Public orders don't have waiter assigned yet
            'status' => 'pendiente_confirmacion', // New status for public orders
            'notes' => $_POST['notes'] ?? null,
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'is_pickup' => isset($_POST['is_pickup']) ? 1 : 0,
            'pickup_datetime' => isset($_POST['pickup_datetime']) ? $_POST['pickup_datetime'] : null
        ];
        
        $customerData = [
            'name' => $_POST['customer_name'],
            'phone' => $_POST['customer_phone'],
            'birthday' => !empty($_POST['customer_birthday']) ? $_POST['customer_birthday'] : null
        ];
        
        try {
            $orderId = $this->orderModel->createPublicOrderWithCustomer($orderData, $items, $customerData);
            
            // Don't update table status for pickup orders or orders without table
            if (!$orderData['is_pickup'] && !empty($orderData['table_id'])) {
                $this->tableModel->update($orderData['table_id'], ['status' => TABLE_OCCUPIED]);
            }
            
            $this->viewPublic('public/order_success', [
                'order_id' => $orderId,
                'is_pickup' => $orderData['is_pickup']
            ]);
        } catch (Exception $e) {
            $dishes = $this->dishModel->findAll(['active' => 1], 'category ASC, name ASC');
            $tables = $this->tableModel->findAll(['active' => 1, 'status' => TABLE_AVAILABLE], 'number ASC');
            
            $this->viewPublic('public/menu', [
                'error' => 'Error al crear el pedido: ' . $e->getMessage(),
                'old' => $_POST,
                'dishes' => $dishes,
                'tables' => $tables
            ]);
        }
    }
    
    private function validatePublicOrderInput($data) {
        $errors = $this->validateInput($data, [
            'customer_name' => ['required' => true, 'max' => 255],
            'customer_phone' => ['required' => true, 'max' => 20]
            // table_id is now optional - removed required validation
        ]);
        
        // Validate birthday format if provided (DD/MM)
        if (!empty($data['customer_birthday'])) {
            if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])$/', $data['customer_birthday'])) {
                $errors['customer_birthday'] = 'Formato de cumpleaños inválido. Use DD/MM (ej: 15/03)';
            } else {
                // Validate that it's a valid date combination
                list($day, $month) = explode('/', $data['customer_birthday']);
                if (!checkdate($month, $day, 2000)) {
                    $errors['customer_birthday'] = 'Fecha de cumpleaños inválida';
                }
            }
        }
        
        // Validate pickup datetime if pickup is selected
        if (isset($data['is_pickup']) && !empty($data['pickup_datetime'])) {
            $pickupTime = strtotime($data['pickup_datetime']);
            $now = time();
            
            // Minimum 30 minutes advance notice
            $minTime = $now + (30 * 60); // 30 minutes from now
            
            // Maximum 30 days advance
            $maxTime = $now + (30 * 24 * 60 * 60); // 30 days from now
            
            if ($pickupTime <= $minTime) {
                $errors['pickup_datetime'] = 'La fecha y hora de pickup debe ser al menos 30 minutos en adelante';
            } elseif ($pickupTime > $maxTime) {
                $errors['pickup_datetime'] = 'La fecha y hora de pickup no puede ser más de 30 días en adelante';
            }
        } elseif (isset($data['is_pickup']) && empty($data['pickup_datetime'])) {
            $errors['pickup_datetime'] = 'Debe seleccionar fecha y hora para pedidos pickup';
        }
        
        return $errors;
    }
    
    protected function viewPublic($viewName, $data = []) {
        // Extract data variables
        extract($data);
        
        // Include public header
        include BASE_PATH . '/views/layouts/public_header.php';
        
        // Include the view
        $viewPath = BASE_PATH . '/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            include BASE_PATH . '/views/errors/404.php';
        }
        
        // Include public footer
        include BASE_PATH . '/views/layouts/public_footer.php';
    }
    
    public function reservations() {
        $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
        
        $this->viewPublic('public/reservations', [
            'tables' => $tables
        ]);
    }
    
    public function reservation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPublicReservation();
        } else {
            $this->redirect('public/reservations');
        }
    }
    
    private function processPublicReservation() {
        $errors = $this->validatePublicReservationInput($_POST);
        
        if (empty($errors)) {
            try {
                $reservationData = [
                    'reservation_datetime' => $_POST['reservation_datetime'],
                    'party_size' => $_POST['party_size'],
                    'notes' => $_POST['notes'] ?? null,
                    'status' => 'pendiente'
                ];
                
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
                
                $this->viewPublic('public/reservation_success', [
                    'reservation_id' => $reservationId
                ]);
                
            } catch (Exception $e) {
                $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
                
                $this->viewPublic('public/reservations', [
                    'error' => 'Error al crear la reservación: ' . $e->getMessage(),
                    'old' => $_POST,
                    'tables' => $tables
                ]);
            }
        } else {
            $tables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            $this->viewPublic('public/reservations', [
                'errors' => $errors,
                'old' => $_POST,
                'tables' => $tables
            ]);
        }
    }
    
    private function validatePublicReservationInput($data) {
        $errors = $this->validateInput($data, [
            'customer_name' => ['required' => true, 'max' => 255],
            'customer_phone' => ['required' => true, 'max' => 20],
            // table_id is now optional - removed required validation
            'reservation_datetime' => ['required' => true],
            'party_size' => ['required' => true, 'min' => 1, 'max' => 20]
        ]);
        
        // Validate birthday format if provided (DD/MM)
        if (!empty($data['customer_birthday'])) {
            if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])$/', $data['customer_birthday'])) {
                $errors['customer_birthday'] = 'Formato de cumpleaños inválido. Use DD/MM (ej: 15/03)';
            } else {
                // Validate that it's a valid date combination
                list($day, $month) = explode('/', $data['customer_birthday']);
                if (!checkdate($month, $day, 2000)) {
                    $errors['customer_birthday'] = 'Fecha de cumpleaños inválida';
                }
            }
        }
        
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
    
    public function getAvailableTablesByDate() {
        // This method handles AJAX requests to get available tables for a specific date (public access)
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
        
        try {
            // Get all active tables
            $allTables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            
            // Filter available tables based on the datetime
            $availableTables = [];
            foreach ($allTables as $table) {
                if ($this->reservationModel->checkTableAvailability([$table['id']], $datetime)) {
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