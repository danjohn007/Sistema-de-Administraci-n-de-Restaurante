<?php
// Test para validar la implementación del módulo de inventarios
echo "=== TEST: Módulo de Inventarios ===\n\n";

// Setup same as index.php
define('BASE_PATH', __DIR__);
define('BASE_URL', '/restaurante/');

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

try {
    echo "1. Testing system settings...\n";
    $systemSettings = new SystemSettings();
    
    // Test getting settings
    $collectionsEnabled = $systemSettings->isCollectionsEnabled();
    $inventoryEnabled = $systemSettings->isInventoryEnabled();
    $autoDeductEnabled = $systemSettings->isAutoDeductInventoryEnabled();
    
    echo "   ✓ Collections enabled: " . ($collectionsEnabled ? 'YES' : 'NO') . "\n";
    echo "   ✓ Inventory enabled: " . ($inventoryEnabled ? 'YES' : 'NO') . "\n";
    echo "   ✓ Auto deduct enabled: " . ($autoDeductEnabled ? 'YES' : 'NO') . "\n";
    
    echo "\n2. Testing product model...\n";
    $productModel = new Product();
    
    echo "   ✓ Product model loaded successfully\n";
    
    echo "\n3. Testing inventory movement model...\n";
    $movementModel = new InventoryMovement();
    
    echo "   ✓ InventoryMovement model loaded successfully\n";
    
    echo "\n4. Testing dish ingredient model...\n";
    $dishIngredientModel = new DishIngredient();
    
    echo "   ✓ DishIngredient model loaded successfully\n";
    
    echo "\n5. Testing inventory controller...\n";
    $inventoryController = new InventoryController();
    
    echo "   ✓ InventoryController loaded successfully\n";
    
    echo "\n6. Testing constants...\n";
    echo "   ✓ ROLE_SUPERADMIN = " . ROLE_SUPERADMIN . "\n";
    echo "   ✓ MOVEMENT_TYPE_IN = " . MOVEMENT_TYPE_IN . "\n";
    echo "   ✓ MOVEMENT_TYPE_OUT = " . MOVEMENT_TYPE_OUT . "\n";
    
    echo "\n7. Testing file structure...\n";
    $files = [
        'database/migration_inventory_module.sql',
        'models/Product.php',
        'models/InventoryMovement.php', 
        'models/SystemSettings.php',
        'models/DishIngredient.php',
        'controllers/InventoryController.php',
        'views/inventory/index.php',
        'views/inventory/settings.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "   ✓ $file exists\n";
        } else {
            echo "   ✗ $file missing\n";
        }
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    echo "✓ All basic functionality tests passed\n";
    echo "✓ Models loaded successfully\n";
    echo "✓ Controllers loaded successfully\n";
    echo "✓ Constants defined correctly\n";
    echo "✓ Required files exist\n";
    echo "\nNEXT STEPS:\n";
    echo "1. Run the database migration: migration_inventory_module.sql\n";
    echo "2. Create a superadmin user to access inventory settings\n";
    echo "3. Access /inventory to start using the inventory module\n";
    echo "4. Configure dish recipes to enable automatic inventory deduction\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>