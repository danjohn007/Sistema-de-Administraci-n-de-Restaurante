<?php
class SystemSettings extends BaseModel {
    protected $table = 'system_settings';
    
    public function getSetting($key, $default = null) {
        $query = "SELECT setting_value FROM {$this->table} WHERE setting_key = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$key]);
        
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    }
    
    public function setSetting($key, $value, $description = null) {
        // Verificar si la configuración ya existe
        $existing = $this->findBy('setting_key', $key);
        
        if ($existing) {
            // Actualizar configuración existente
            $data = ['setting_value' => $value];
            if ($description !== null) {
                $data['description'] = $description;
            }
            return $this->update($existing['id'], $data);
        } else {
            // Crear nueva configuración
            return $this->create([
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description
            ]);
        }
    }
    
    public function getAllSettings() {
        $query = "SELECT * FROM {$this->table} ORDER BY setting_key ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $settings = $stmt->fetchAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    public function getSettingsWithDetails() {
        $query = "SELECT * FROM {$this->table} ORDER BY setting_key ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function isCollectionsEnabled() {
        return $this->getSetting('collections_enabled', '1') === '1';
    }
    
    public function isInventoryEnabled() {
        return $this->getSetting('inventory_enabled', '1') === '1';
    }
    
    public function isAutoDeductInventoryEnabled() {
        return $this->getSetting('auto_deduct_inventory', '1') === '1';
    }
    
    public function enableCollections() {
        return $this->setSetting('collections_enabled', '1');
    }
    
    public function disableCollections() {
        return $this->setSetting('collections_enabled', '0');
    }
    
    public function enableInventory() {
        return $this->setSetting('inventory_enabled', '1');
    }
    
    public function disableInventory() {
        return $this->setSetting('inventory_enabled', '0');
    }
    
    public function enableAutoDeductInventory() {
        return $this->setSetting('auto_deduct_inventory', '1');
    }
    
    public function disableAutoDeductInventory() {
        return $this->setSetting('auto_deduct_inventory', '0');
    }
    
    // Métodos auxiliares para configuraciones específicas
    public function getModuleSettings() {
        $settings = [];
        
        $moduleKeys = [
            'collections_enabled',
            'inventory_enabled', 
            'auto_deduct_inventory'
        ];
        
        foreach ($moduleKeys as $key) {
            $settings[$key] = $this->getSetting($key, '1');
        }
        
        return $settings;
    }
    
    public function updateModuleSettings($settings) {
        try {
            $this->db->beginTransaction();
            
            $validKeys = [
                'collections_enabled',
                'inventory_enabled',
                'auto_deduct_inventory'
            ];
            
            foreach ($settings as $key => $value) {
                if (in_array($key, $validKeys)) {
                    $this->setSetting($key, $value);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
?>