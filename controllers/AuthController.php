<?php
class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function index() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        $this->login();
    }
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        } else {
            $this->view('auth/login');
        }
    }
    
    private function processLogin() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        $errors = $this->validateInput($_POST, [
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 6]
        ]);
        
        if (!empty($errors)) {
            $this->view('auth/login', [
                'errors' => $errors,
                'old_email' => $email
            ]);
            return;
        }
        
        // Authenticate user
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            // Set session data (trim role to avoid whitespace issues)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = trim($user['role']);
            $_SESSION['last_activity'] = time();
            
            $this->redirect('dashboard', 'success', 'Bienvenido al sistema, ' . $user['name']);
        } else {
            $this->view('auth/login', [
                'error' => 'Credenciales inválidas',
                'old_email' => $email
            ]);
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('auth/login', 'info', 'Sesión cerrada correctamente');
    }
    
    public function register() {
        $this->requireRole(ROLE_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegister();
        } else {
            $this->view('auth/register');
        }
    }
    
    private function processRegister() {
        // Validate input
        $errors = $this->validateInput($_POST, [
            'name' => ['required' => true, 'max' => 255],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 6],
            'password_confirmation' => ['required' => true],
            'role' => ['required' => true]
        ]);
        
        // Check password confirmation
        if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden';
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($_POST['email'] ?? '')) {
            $errors['email'] = 'Este email ya está registrado';
        }
        
        if (!empty($errors)) {
            $this->view('auth/register', [
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }
        
        // Create user
        $userData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'role' => $_POST['role']
        ];
        
        try {
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                $this->redirect('users', 'success', 'Usuario creado correctamente');
            } else {
                throw new Exception('Error al crear el usuario');
            }
        } catch (Exception $e) {
            $this->view('auth/register', [
                'error' => 'Error al crear el usuario: ' . $e->getMessage(),
                'old' => $_POST
            ]);
        }
    }
    
    public function changePassword() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processChangePassword();
        } else {
            $this->view('auth/change_password');
        }
    }
    
    private function processChangePassword() {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $errors = $this->validateInput($_POST, [
            'current_password' => ['required' => true],
            'new_password' => ['required' => true, 'min' => 6],
            'confirm_password' => ['required' => true]
        ]);
        
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Las contraseñas nuevas no coinciden';
        }
        
        if (!empty($errors)) {
            $this->view('auth/change_password', ['errors' => $errors]);
            return;
        }
        
        // Verify current password
        $user = $this->userModel->find($_SESSION['user_id']);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $this->view('auth/change_password', [
                'error' => 'La contraseña actual es incorrecta'
            ]);
            return;
        }
        
        // Update password
        try {
            if ($this->userModel->updatePassword($user['id'], $newPassword)) {
                $this->redirect('dashboard', 'success', 'Contraseña actualizada correctamente');
            } else {
                throw new Exception('Error al actualizar la contraseña');
            }
        } catch (Exception $e) {
            $this->view('auth/change_password', [
                'error' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ]);
        }
    }
}
?>