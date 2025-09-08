<?php
session_start();

// Define base path for the application
define('BASE_PATH', __DIR__);
define('BASE_URL', '/gestorest/');

// Include configuration
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models', 'core'];
    
    foreach ($directories as $directory) {
        $file = BASE_PATH . '/' . $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Get URL from .htaccess rewrite
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

// Parse URL
$urlParts = $url ? explode('/', $url) : ['auth'];

// Get controller, method and parameters
$controllerName = !empty($urlParts[0]) ? str_replace(' ', '', ucwords(str_replace('_', ' ', $urlParts[0]))) . 'Controller' : 'AuthController';
$methodName = !empty($urlParts[1]) ? $urlParts[1] : 'index';
$params = array_slice($urlParts, 2);

// Check if controller exists
$controllerPath = BASE_PATH . '/controllers/' . $controllerName . '.php';

if (!file_exists($controllerPath)) {
    // Default to auth controller if not found
    $controllerName = 'AuthController';
    $methodName = 'index';
    $params = [];
}

// Include and instantiate controller
require_once BASE_PATH . '/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

// Check if method exists
if (!method_exists($controller, $methodName)) {
    $methodName = 'index';
}

// Call the method with parameters
call_user_func_array([$controller, $methodName], $params);
?>
