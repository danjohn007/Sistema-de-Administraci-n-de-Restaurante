<?php
class ProfileController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->userModel = new User();
    }
    
    public function index() {
        $user = $this->getCurrentUser();
        $userData = $this->userModel->find($user['id']);
        
        if (!$userData) {
            $this->redirect('dashboard', 'error', 'Usuario no encontrado');
            return;
        }
        
        $this->view('profile/index', [
            'user' => $userData
        ]);
    }
    
    public function edit() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($user['id']);
        } else {
            $userData = $this->userModel->find($user['id']);
            
            if (!$userData) {
                $this->redirect('dashboard', 'error', 'Usuario no encontrado');
                return;
            }
            
            $this->view('profile/edit', [
                'user' => $userData
            ]);
        }
    }
    
    private function processEdit($userId) {
        $errors = $this->validateProfileInput($_POST, $userId);
        
        if (!empty($errors)) {
            $userData = $this->userModel->find($userId);
            $this->view('profile/edit', [
                'errors' => $errors,
                'user' => $userData,
                'old' => $_POST
            ]);
            return;
        }
        
        $profileData = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email'])
        ];
        
        try {
            $success = $this->userModel->update($userId, $profileData);
            
            if ($success) {
                // Update session data
                $_SESSION['user_name'] = $profileData['name'];
                $_SESSION['user_email'] = $profileData['email'];
                
                $this->redirect('profile', 'success', 'Perfil actualizado correctamente');
            } else {
                throw new Exception('Error al actualizar el perfil');
            }
        } catch (Exception $e) {
            $userData = $this->userModel->find($userId);
            $this->view('profile/edit', [
                'error' => 'Error al actualizar el perfil: ' . $e->getMessage(),
                'user' => $userData,
                'old' => $_POST
            ]);
        }
    }
    
    private function validateProfileInput($data, $excludeId = null) {
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'min' => 2],
            'email' => ['required' => true, 'email' => true]
        ]);
        
        // Check if email already exists (excluding current user)
        if ($this->userModel->emailExists($data['email'] ?? '', $excludeId)) {
            $errors['email'] = 'Este email ya está registrado';
        }
        
        return $errors;
    }
    
    public function changePassword() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processChangePassword($user['id']);
        } else {
            $userData = $this->userModel->find($user['id']);
            
            if (!$userData) {
                $this->redirect('dashboard', 'error', 'Usuario no encontrado');
                return;
            }
            
            $this->view('profile/change_password', [
                'user' => $userData
            ]);
        }
    }
    
    private function processChangePassword($userId) {
        $errors = $this->validatePasswordInput($_POST);
        
        if (!empty($errors)) {
            $userData = $this->userModel->find($userId);
            $this->view('profile/change_password', [
                'errors' => $errors,
                'user' => $userData
            ]);
            return;
        }
        
        // Verify current password
        $userData = $this->userModel->find($userId);
        if (!password_verify($_POST['current_password'], $userData['password'])) {
            $userData = $this->userModel->find($userId);
            $this->view('profile/change_password', [
                'errors' => ['current_password' => 'La contraseña actual es incorrecta'],
                'user' => $userData
            ]);
            return;
        }
        
        try {
            $success = $this->userModel->updatePassword($userId, $_POST['password']);
            
            if ($success) {
                $this->redirect('profile', 'success', 'Contraseña actualizada correctamente');
            } else {
                throw new Exception('Error al actualizar la contraseña');
            }
        } catch (Exception $e) {
            $userData = $this->userModel->find($userId);
            $this->view('profile/change_password', [
                'error' => 'Error al actualizar la contraseña: ' . $e->getMessage(),
                'user' => $userData
            ]);
        }
    }
    
    private function validatePasswordInput($data) {
        $errors = $this->validateInput($data, [
            'current_password' => ['required' => true],
            'password' => ['required' => true, 'min' => 6],
            'password_confirmation' => ['required' => true]
        ]);
        
        if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden';
        }
        
        return $errors;
    }
}
?>