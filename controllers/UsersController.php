<?php
class UsersController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function index() {
        $this->requireRole(ROLE_ADMIN);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $conditions = ['active' => 1];
        
        if ($roleFilter) {
            $conditions['role'] = $roleFilter;
        }
        
        // If search is provided, we need a custom query
        if ($search) {
            $query = "SELECT * FROM users WHERE active = 1";
            $params = [];
            
            if ($roleFilter) {
                $query .= " AND role = ?";
                $params[] = $roleFilter;
            }
            
            $query .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            
            $query .= " ORDER BY name ASC";
            
            // Manual pagination for search
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
            $stmt = $this->userModel->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // Get data
            $query .= " LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $this->userModel->db->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            $result = [
                'data' => $users,
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
            $result = $this->userModel->paginate($page, ITEMS_PER_PAGE, $conditions, 'name ASC');
        }
        
        $this->view('users/index', [
            'users' => $result['data'],
            'pagination' => $result['pagination'],
            'roleFilter' => $roleFilter,
            'search' => $search,
            'roles' => [
                ROLE_ADMIN => 'Administrador',
                ROLE_WAITER => 'Mesero',
                ROLE_CASHIER => 'Cajero'
            ]
        ]);
    }
    
    public function create() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
        } else {
            $this->view('users/create', [
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
        }
    }
    
    private function processCreate() {
        $errors = $this->validateUserInput($_POST);
        
        if (!empty($errors)) {
            $this->view('users/create', [
                'errors' => $errors,
                'old' => $_POST,
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
            return;
        }
        
        $userData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'role' => trim($_POST['role']) // Trim role to avoid whitespace issues
        ];
        
        // Log the user creation attempt for debugging
        error_log("UsersController::processCreate - Attempting to create user: " . json_encode([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ]));
        error_log("UsersController::processCreate - Current user role: " . ($_SESSION['user_role'] ?? 'NOT_SET'));
        
        try {
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                error_log("UsersController::processCreate - SUCCESS: User created with ID: $userId");
                $this->redirect('users', 'success', 'Usuario creado correctamente');
            } else {
                error_log("UsersController::processCreate - FAILED: User creation returned false");
                throw new Exception('Error al crear el usuario - no se pudo insertar en la base de datos');
            }
        } catch (Exception $e) {
            error_log("UsersController::processCreate - EXCEPTION: " . $e->getMessage());
            $this->view('users/create', [
                'error' => 'Error al crear el usuario: ' . $e->getMessage(),
                'old' => $_POST,
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
        }
    }
    
    public function edit($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $user = $this->userModel->find($id);
        if (!$user || !$user['active']) {
            $this->redirect('users', 'error', 'Usuario no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($id);
        } else {
            $this->view('users/edit', [
                'user' => $user,
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
        }
    }
    
    private function processEdit($id) {
        $errors = $this->validateUserInput($_POST, $id);
        
        $user = $this->userModel->find($id);
        
        if (!empty($errors)) {
            $this->view('users/edit', [
                'errors' => $errors,
                'user' => $user,
                'old' => $_POST,
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
            return;
        }
        
        $userData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'role' => $_POST['role']
        ];
        
        try {
            $success = $this->userModel->update($id, $userData);
            
            if ($success) {
                $this->redirect('users', 'success', 'Usuario actualizado correctamente');
            } else {
                throw new Exception('Error al actualizar el usuario');
            }
        } catch (Exception $e) {
            $this->view('users/edit', [
                'error' => 'Error al actualizar el usuario: ' . $e->getMessage(),
                'user' => $user,
                'old' => $_POST,
                'roles' => [
                    ROLE_ADMIN => 'Administrador',
                    ROLE_WAITER => 'Mesero',
                    ROLE_CASHIER => 'Cajero'
                ]
            ]);
        }
    }
    
    public function delete($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $user = $this->userModel->find($id);
        if (!$user || !$user['active']) {
            $this->redirect('users', 'error', 'Usuario no encontrado');
            return;
        }
        
        // Prevent self-deletion
        if ($user['id'] == $_SESSION['user_id']) {
            $this->redirect('users', 'error', 'No puedes eliminar tu propio usuario');
            return;
        }
        
        try {
            $success = $this->userModel->softDelete($id);
            
            if ($success) {
                $this->redirect('users', 'success', 'Usuario eliminado correctamente');
            } else {
                throw new Exception('Error al eliminar el usuario');
            }
        } catch (Exception $e) {
            $this->redirect('users', 'error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
    
    public function changePassword($id) {
        $this->requireRole(ROLE_ADMIN);
        
        $user = $this->userModel->find($id);
        if (!$user || !$user['active']) {
            $this->redirect('users', 'error', 'Usuario no encontrado');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processChangePassword($id);
        } else {
            $this->view('users/change_password', [
                'user' => $user
            ]);
        }
    }
    
    private function processChangePassword($id) {
        $errors = $this->validateInput($_POST, [
            'password' => ['required' => true, 'min' => 6],
            'password_confirmation' => ['required' => true]
        ]);
        
        if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden';
        }
        
        $user = $this->userModel->find($id);
        
        if (!empty($errors)) {
            $this->view('users/change_password', [
                'errors' => $errors,
                'user' => $user
            ]);
            return;
        }
        
        try {
            $success = $this->userModel->updatePassword($id, $_POST['password']);
            
            if ($success) {
                $this->redirect('users', 'success', 'Contraseña actualizada correctamente');
            } else {
                throw new Exception('Error al actualizar la contraseña');
            }
        } catch (Exception $e) {
            $this->view('users/change_password', [
                'error' => 'Error al actualizar la contraseña: ' . $e->getMessage(),
                'user' => $user
            ]);
        }
    }
    
    private function validateUserInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'max' => 255],
            'email' => ['required' => true, 'email' => true],
            'role' => ['required' => true]
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
        if ($this->userModel->emailExists($data['email'] ?? '', $excludeId)) {
            $errors['email'] = 'Este email ya está registrado';
        }
        
        // Validate role
        $validRoles = [ROLE_ADMIN, ROLE_WAITER, ROLE_CASHIER];
        if (!in_array($data['role'] ?? '', $validRoles)) {
            $errors['role'] = 'Rol inválido';
        }
        
        return $errors;
    }
}
?>