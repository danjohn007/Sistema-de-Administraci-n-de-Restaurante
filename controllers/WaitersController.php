<?php
class WaitersController extends BaseController {
    private $waiterModel;
    private $userModel;
    private $tableModel;
    
    public function __construct() {
        parent::__construct();
        $this->waiterModel = new Waiter();
        $this->userModel = new User();
        $this->tableModel = new Table();
    }
    
    public function index() {
        $this->requireRole(ROLE_ADMIN);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Get waiters with user data
        if ($search) {
            $query = "SELECT w.*, u.name, u.email, u.active as user_active 
                      FROM waiters w 
                      JOIN users u ON w.user_id = u.id 
                      WHERE w.active = 1 
                      AND (u.name LIKE ? OR u.email LIKE ? OR w.employee_code LIKE ?)
                      ORDER BY u.name ASC";
            
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
            
            // Manual pagination for search
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total 
                           FROM waiters w 
                           JOIN users u ON w.user_id = u.id 
                           WHERE w.active = 1 
                           AND (u.name LIKE ? OR u.email LIKE ? OR w.employee_code LIKE ?)";
            $stmt = $this->waiterModel->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // Get data
            $query .= " LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $this->waiterModel->db->prepare($query);
            $stmt->execute($params);
            $waiters = $stmt->fetchAll();
            
            $result = [
                'data' => $waiters,
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
            $waiters = $this->waiterModel->getWaitersWithUsers();
            $perPage = ITEMS_PER_PAGE;
            $total = count($waiters);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            $result = [
                'data' => array_slice($waiters, $offset, $perPage),
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
        
        $this->view('waiters/index', [
            'waiters' => $result['data'],
            'pagination' => $result['pagination'],
            'search' => $search
        ]);
    }
    
    public function create() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            $this->view('waiters/create');
        }
    }
    
    private function processCreate() {
        $errors = $this->validateWaiterInput($_POST);
        
        if (!empty($errors)) {
            $this->view('waiters/create', [
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }
        
        $userData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'role' => ROLE_WAITER
        ];
        
        $waiterData = [
            'employee_code' => trim($_POST['employee_code']),
            'phone' => trim($_POST['phone']) ?: null
        ];
        
        try {
            $waiterId = $this->waiterModel->createWaiter($userData, $waiterData);
            
            if ($waiterId) {
                $this->redirect('waiters', 'success', 'Mesero creado correctamente');
            } else {
                throw new Exception('Error al crear el mesero');
            }
        } catch (Exception $e) {
            $this->view('waiters/create', [
                'error' => 'Error al crear el mesero: ' . $e->getMessage(),
                'old' => $_POST
            ]);
        }
    }
    
    public function edit($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $waiter = $this->getWaiterWithUser($id);
        if (!$waiter) {
            $this->redirect('waiters', 'error', 'Mesero no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $this->view('waiters/edit', [
                'waiter' => $waiter
            ]);
        }
    }
    
    private function processEdit($id) {
        $waiter = $this->getWaiterWithUser($id);
        $errors = $this->validateWaiterInput($_POST, $id);
        
        if (!empty($errors)) {
            $this->view('waiters/edit', [
                'errors' => $errors,
                'waiter' => $waiter,
                'old' => $_POST
            ]);
            return;
        }
        
        $userData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email'])
        ];
        
        $waiterData = [
            'employee_code' => trim($_POST['employee_code']),
            'phone' => trim($_POST['phone']) ?: null
        ];
        
        try {
            $this->waiterModel->db->beginTransaction();
            
            // Update user
            $userUpdated = $this->userModel->update($waiter['user_id'], $userData);
            if (!$userUpdated) {
                throw new Exception('Error al actualizar los datos del usuario');
            }
            
            // Update waiter
            $waiterUpdated = $this->waiterModel->update($id, $waiterData);
            if (!$waiterUpdated) {
                throw new Exception('Error al actualizar los datos del mesero');
            }
            
            $this->waiterModel->db->commit();
            $this->redirect('waiters', 'success', 'Mesero actualizado correctamente');
            
        } catch (Exception $e) {
            $this->waiterModel->db->rollback();
            $this->view('waiters/edit', [
                'error' => 'Error al actualizar el mesero: ' . $e->getMessage(),
                'waiter' => $waiter,
                'old' => $_POST
            ]);
        }
    }
    
    public function delete($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $waiter = $this->waiterModel->find($id);
        if (!$waiter || !$waiter['active']) {
            $this->redirect('waiters', 'error', 'Mesero no encontrado');
            return;
        }
        
        try {
            $this->waiterModel->db->beginTransaction();
            
            // Check if waiter has assigned tables
            $tables = $this->tableModel->findAll(['waiter_id' => $id, 'active' => 1]);
            if (!empty($tables)) {
                throw new Exception('No se puede eliminar el mesero porque tiene mesas asignadas');
            }
            
            // Soft delete waiter
            $success = $this->waiterModel->softDelete($id);
            if (!$success) {
                throw new Exception('Error al eliminar el mesero');
            }
            
            // Deactivate user
            $userSuccess = $this->userModel->softDelete($waiter['user_id']);
            if (!$userSuccess) {
                throw new Exception('Error al desactivar el usuario del mesero');
            }
            
            $this->waiterModel->db->commit();
            $this->redirect('waiters', 'success', 'Mesero eliminado correctamente');
            
        } catch (Exception $e) {
            $this->waiterModel->db->rollback();
            $this->redirect('waiters', 'error', 'Error al eliminar el mesero: ' . $e->getMessage());
        }
    }
    
    public function assignTables($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $waiter = $this->getWaiterWithUser($id);
        if (!$waiter) {
            $this->redirect('waiters', 'error', 'Mesero no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processAssignTables($id);
        } else {
            // Show ALL tables to waiters now, not just available ones
            $allTables = $this->tableModel->findAll(['active' => 1], 'number ASC');
            // Get tables currently "assigned" to this waiter (those they have blocked)
            $assignedTables = $this->tableModel->findAll(['waiter_id' => $id, 'active' => 1]);
            
            $this->view('waiters/assign_tables', [
                'waiter' => $waiter,
                'available_tables' => $allTables, // Changed: now all tables are "available" for assignment
                'assigned_tables' => $assignedTables
            ]);
        }
    }
    
    private function processAssignTables($waiterId) {
        $tableIds = $_POST['table_ids'] ?? [];
        
        try {
            $this->tableModel->db->beginTransaction();
            
            // First, remove all current table assignments for this waiter
            $currentTables = $this->tableModel->findAll(['waiter_id' => $waiterId, 'active' => 1]);
            foreach ($currentTables as $table) {
                $this->tableModel->update($table['id'], [
                    'waiter_id' => null,
                    'status' => TABLE_AVAILABLE
                ]);
            }
            
            // Assign selected tables
            foreach ($tableIds as $tableId) {
                $table = $this->tableModel->find($tableId);
                if ($table && $table['active'] && $table['status'] === TABLE_AVAILABLE) {
                    $this->tableModel->assignWaiter($tableId, $waiterId);
                }
            }
            
            $this->tableModel->db->commit();
            $this->redirect('waiters', 'success', 'Mesas asignadas correctamente');
            
        } catch (Exception $e) {
            $this->tableModel->db->rollback();
            $this->redirect('waiters/assignTables/' . $waiterId, 'error', 'Error al asignar mesas: ' . $e->getMessage());
        }
    }
    
    private function getWaiterWithUser($waiterId) {
        $query = "SELECT w.*, u.name, u.email, u.active as user_active 
                  FROM waiters w 
                  JOIN users u ON w.user_id = u.id 
                  WHERE w.id = ? AND w.active = 1";
        
        $stmt = $this->waiterModel->db->prepare($query);
        $stmt->execute([$waiterId]);
        
        return $stmt->fetch();
    }
    
    private function validateWaiterInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'max' => 255],
            'email' => ['required' => true, 'email' => true],
            'employee_code' => ['required' => true, 'max' => 20]
        ]);
        
        // Validate password only on create
        if ($excludeId === null) {
            $passwordErrors = $this->validateInput($data, [
                'password' => ['required' => true, 'min' => 6],
                'password_confirmation' => ['required' => true]
            ]);
            $errors = array_merge($errors, $passwordErrors);
            
            if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
                $errors['password_confirmation'] = 'Las contraseñas no coinciden';
            }
        }
        
        // Check if email already exists
        if ($excludeId !== null) {
            // For edit, get current waiter's user_id
            $waiter = $this->waiterModel->find($excludeId);
            $excludeUserId = $waiter ? $waiter['user_id'] : null;
        } else {
            $excludeUserId = null;
        }
        
        if ($this->userModel->emailExists($data['email'] ?? '', $excludeUserId)) {
            $errors['email'] = 'Este email ya está registrado';
        }
        
        // Check if employee code already exists
        if ($this->employeeCodeExists($data['employee_code'] ?? '', $excludeId)) {
            $errors['employee_code'] = 'Este código de empleado ya existe';
        }
        
        return $errors;
    }
    
    private function employeeCodeExists($employeeCode, $excludeId = null) {
        $query = "SELECT id FROM waiters WHERE employee_code = ? AND active = 1";
        $params = [$employeeCode];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->waiterModel->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
}
?>