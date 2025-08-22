<?php
class User extends BaseModel {
    protected $table = 'users';
    public $db; // Make db property public for UserController access
    
    public function authenticate($email, $password) {
        $user = $this->findBy('email', $email);
        
        if ($user && password_verify($password, $user['password']) && $user['active']) {
            return $user;
        }
        
        return false;
    }
    
    public function createUser($data) {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], HASH_ALGO);
        }
        
        return $this->create($data);
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, HASH_ALGO);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function getUsersByRole($role) {
        return $this->findAll(['role' => $role, 'active' => 1], 'name ASC');
    }
    
    public function emailExists($email, $excludeId = null) {
        $query = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
}
?>