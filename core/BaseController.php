<?php
abstract class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->checkSession();
    }
    
    protected function checkSession() {
        // Session timeout check
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
        }
        $_SESSION['last_activity'] = time();
    }
    
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }
    
    protected function requireRole($allowedRoles) {
        $this->requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            $this->redirect('dashboard', 'error', 'No tienes permisos para acceder a esta sección');
        }
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    protected function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    protected function redirect($url, $type = null, $message = null) {
        if ($message) {
            $_SESSION['flash_' . ($type ?? 'info')] = $message;
        }
        
        $baseUrl = rtrim(BASE_URL, '/');
        header("Location: {$baseUrl}/{$url}");
        exit();
    }
    
    protected function view($viewName, $data = []) {
        // Extract data variables
        extract($data);
        
        // Include header
        include BASE_PATH . '/views/layouts/header.php';
        
        // Include the view
        $viewPath = BASE_PATH . '/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            include BASE_PATH . '/views/errors/404.php';
        }
        
        // Include footer
        include BASE_PATH . '/views/layouts/footer.php';
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function getFlashMessage($type = 'info') {
        $key = 'flash_' . $type;
        if (isset($_SESSION[$key])) {
            $message = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $message;
        }
        return null;
    }
    
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }
    
    protected function logout() {
        session_destroy();
        $this->redirect('auth/login');
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = $rule['message'] ?? "El campo {$field} es requerido";
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "El campo {$field} debe ser un email válido";
                }
                
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    $errors[$field] = "El campo {$field} debe tener al menos {$rule['min']} caracteres";
                }
                
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    $errors[$field] = "El campo {$field} no puede tener más de {$rule['max']} caracteres";
                }
                
                if (isset($rule['numeric']) && $rule['numeric'] && !is_numeric($value)) {
                    $errors[$field] = "El campo {$field} debe ser numérico";
                }
            }
        }
        
        return $errors;
    }
}
?>