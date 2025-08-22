<?php
class TableController extends BaseController {
    private $tableModel;
    private $waiterModel;
    
    public function __construct() {
        parent::__construct();
        $this->tableModel = new Table();
        $this->waiterModel = new Waiter();
    }
    
    public function index() {
        $this->requireRole(ROLE_ADMIN);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Build query conditions
        $conditions = ['active' => 1];
        
        if ($statusFilter) {
            $conditions['status'] = $statusFilter;
        }
        
        // If search is provided, we need a custom query
        if ($search) {
            $query = "SELECT t.*, w.employee_code, u.name as waiter_name 
                      FROM tables t 
                      LEFT JOIN waiters w ON t.waiter_id = w.id 
                      LEFT JOIN users u ON w.user_id = u.id 
                      WHERE t.active = 1";
            $params = [];
            
            if ($statusFilter) {
                $query .= " AND t.status = ?";
                $params[] = $statusFilter;
            }
            
            $query .= " AND (CAST(t.number AS CHAR) LIKE ? OR u.name LIKE ? OR w.employee_code LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            
            $query .= " ORDER BY t.number ASC";
            
            // Manual pagination for search
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countQuery = str_replace("SELECT t.*, w.employee_code, u.name as waiter_name", "SELECT COUNT(*) as total", $query);
            $stmt = $this->tableModel->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // Get data
            $query .= " LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $this->tableModel->db->prepare($query);
            $stmt->execute($params);
            $tables = $stmt->fetchAll();
            
            $result = [
                'data' => $tables,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_next' => $page < ceil($total / $perPage),
                    'has_prev' => $page > 1
                ]
            ];
        } else {
            // Use existing method for non-search queries with pagination
            $tablesWithWaiters = $this->tableModel->getTablesWithWaiters();
            
            // Apply status filter
            if ($statusFilter) {
                $tablesWithWaiters = array_filter($tablesWithWaiters, function($table) use ($statusFilter) {
                    return $table['status'] === $statusFilter;
                });
            }
            
            $perPage = ITEMS_PER_PAGE;
            $total = count($tablesWithWaiters);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            $result = [
                'data' => array_slice($tablesWithWaiters, $offset, $perPage),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ];
        }
        
        // Get table stats
        $stats = $this->tableModel->getTableStats();
        
        $this->view('tables/index', [
            'tables' => $result['data'],
            'pagination' => $result['pagination'],
            'statusFilter' => $statusFilter,
            'search' => $search,
            'stats' => $stats,
            'statuses' => [
                TABLE_AVAILABLE => 'Disponible',
                TABLE_OCCUPIED => 'Ocupada',
                TABLE_BILL_REQUESTED => 'Cuenta Solicitada',
                'cerrada' => 'Cerrada'
            ]
        ]);
    }
    
    public function create() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/create', [
                'waiters' => $waiters
            ]);
        }
    }
    
    private function processCreate() {
        $errors = $this->validateTableInput($_POST);
        
        if (!empty($errors)) {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/create', [
                'errors' => $errors,
                'old' => $_POST,
                'waiters' => $waiters
            ]);
            return;
        }
        
        $tableData = [
            'number' => (int)$_POST['number'],
            'capacity' => (int)$_POST['capacity'],
            'status' => TABLE_AVAILABLE,
            'waiter_id' => !empty($_POST['waiter_id']) ? (int)$_POST['waiter_id'] : null
        ];
        
        try {
            $tableId = $this->tableModel->create($tableData);
            
            if ($tableId) {
                $this->redirect('tables', 'success', 'Mesa creada correctamente');
            } else {
                throw new Exception('Error al crear la mesa');
            }
        } catch (Exception $e) {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/create', [
                'error' => 'Error al crear la mesa: ' . $e->getMessage(),
                'old' => $_POST,
                'waiters' => $waiters
            ]);
        }
    }
    
    public function edit($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $table = $this->tableModel->find($id);
        if (!$table || !$table['active']) {
            $this->redirect('tables', 'error', 'Mesa no encontrada');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/edit', [
                'table' => $table,
                'waiters' => $waiters
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateTableInput($_POST, $id);
        
        $table = $this->tableModel->find($id);
        
        if (!empty($errors)) {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/edit', [
                'errors' => $errors,
                'table' => $table,
                'old' => $_POST,
                'waiters' => $waiters
            ]);
            return;
        }
        
        $tableData = [
            'number' => (int)$_POST['number'],
            'capacity' => (int)$_POST['capacity'],
            'waiter_id' => !empty($_POST['waiter_id']) ? (int)$_POST['waiter_id'] : null
        ];
        
        try {
            $success = $this->tableModel->update($id, $tableData);
            
            if ($success) {
                $this->redirect('tables', 'success', 'Mesa actualizada correctamente');
            } else {
                throw new Exception('Error al actualizar la mesa');
            }
        } catch (Exception $e) {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/edit', [
                'error' => 'Error al actualizar la mesa: ' . $e->getMessage(),
                'table' => $table,
                'old' => $_POST,
                'waiters' => $waiters
            ]);
        }
    }
    
    public function delete($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $table = $this->tableModel->find($id);
        if (!$table || !$table['active']) {
            $this->redirect('tables', 'error', 'Mesa no encontrada');
            return;
        }
        
        // Check if table is occupied or has active orders
        if ($table['status'] === TABLE_OCCUPIED || $table['status'] === TABLE_BILL_REQUESTED) {
            $this->redirect('tables', 'error', 'No se puede eliminar una mesa que está ocupada o con cuenta solicitada');
            return;
        }
        
        try {
            $success = $this->tableModel->softDelete($id);
            
            if ($success) {
                $this->redirect('tables', 'success', 'Mesa eliminada correctamente');
            } else {
                throw new Exception('Error al eliminar la mesa');
            }
        } catch (Exception $e) {
            $this->redirect('tables', 'error', 'Error al eliminar la mesa: ' . $e->getMessage());
        }
    }
    
    public function changeStatus($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $table = $this->tableModel->find($id);
        if (!$table || !$table['active']) {
            $this->redirect('tables', 'error', 'Mesa no encontrada');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processChangeStatus($id);
        } else {
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/change_status', [
                'table' => $table,
                'waiters' => $waiters,
                'statuses' => [
                    TABLE_AVAILABLE => 'Disponible',
                    TABLE_OCCUPIED => 'Ocupada',
                    TABLE_BILL_REQUESTED => 'Cuenta Solicitada',
                    'cerrada' => 'Cerrada'
                ]
            ]);
        }
    }
    
    private function processChangeStatus($id) {
        $status = $_POST['status'] ?? '';
        $waiterId = !empty($_POST['waiter_id']) ? (int)$_POST['waiter_id'] : null;
        
        $validStatuses = [TABLE_AVAILABLE, TABLE_OCCUPIED, TABLE_BILL_REQUESTED, 'cerrada'];
        
        if (!in_array($status, $validStatuses)) {
            $this->redirect('tables/changeStatus/' . $id, 'error', 'Estado inválido');
            return;
        }
        
        // If status is occupied, waiter is required
        if ($status === TABLE_OCCUPIED && !$waiterId) {
            $table = $this->tableModel->find($id);
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $this->view('tables/change_status', [
                'table' => $table,
                'waiters' => $waiters,
                'error' => 'Debe seleccionar un mesero para marcar la mesa como ocupada',
                'old' => $_POST,
                'statuses' => [
                    TABLE_AVAILABLE => 'Disponible',
                    TABLE_OCCUPIED => 'Ocupada',
                    TABLE_BILL_REQUESTED => 'Cuenta Solicitada',
                    'cerrada' => 'Cerrada'
                ]
            ]);
            return;
        }
        
        // If status is available, remove waiter assignment
        if ($status === TABLE_AVAILABLE) {
            $waiterId = null;
        }
        
        try {
            $success = $this->tableModel->updateTableStatus($id, $status, $waiterId);
            
            if ($success) {
                $this->redirect('tables', 'success', 'Estado de la mesa actualizado correctamente');
            } else {
                throw new Exception('Error al actualizar el estado de la mesa');
            }
        } catch (Exception $e) {
            $this->redirect('tables/changeStatus/' . $id, 'error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }
    
    private function validateTableInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'number' => ['required' => true, 'numeric' => true],
            'capacity' => ['required' => true, 'numeric' => true]
        ]);
        
        // Additional validations
        $number = (int)($data['number'] ?? 0);
        $capacity = (int)($data['capacity'] ?? 0);
        
        if ($number <= 0) {
            $errors['number'] = 'El número de mesa debe ser mayor a 0';
        }
        
        if ($capacity <= 0) {
            $errors['capacity'] = 'La capacidad debe ser mayor a 0';
        }
        
        if ($capacity > 20) {
            $errors['capacity'] = 'La capacidad no puede ser mayor a 20 personas';
        }
        
        // Check if table number already exists
        if ($this->tableModel->numberExists($number, $excludeId)) {
            $errors['number'] = 'Ya existe una mesa con este número';
        }
        
        // Validate waiter if provided
        if (!empty($data['waiter_id'])) {
            $waiter = $this->waiterModel->find($data['waiter_id']);
            if (!$waiter || !$waiter['active']) {
                $errors['waiter_id'] = 'Mesero no válido';
            }
        }
        
        return $errors;
    }
}
?>