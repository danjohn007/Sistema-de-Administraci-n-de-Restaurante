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
}
?>