<?php
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findBy($field, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }
    
    public function findAll($conditions = [], $orderBy = null, $limit = null) {
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $query = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->prepare($query);
            
            if ($stmt->execute(array_values($data))) {
                $insertId = $this->db->lastInsertId();
                // Log successful creation for debugging
                error_log("BaseModel::create SUCCESS - Table: {$this->table}, ID: $insertId, Data: " . json_encode($data));
                return $insertId;
            } else {
                // Log execution failure
                $errorInfo = $stmt->errorInfo();
                error_log("BaseModel::create EXECUTION FAILED - Table: {$this->table}, Error: " . json_encode($errorInfo) . ", Query: $query, Data: " . json_encode($data));
                return false;
            }
        } catch (Exception $e) {
            // Log exception
            error_log("BaseModel::create EXCEPTION - Table: {$this->table}, Error: " . $e->getMessage() . ", Query: $query, Data: " . json_encode($data));
            return false;
        }
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $setClauses = [];
        
        foreach ($fields as $field) {
            $setClauses[] = "{$field} = ?";
        }
        
        $query = "UPDATE {$this->table} SET " . implode(',', $setClauses) . " WHERE {$this->primaryKey} = ?";
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    public function softDelete($id) {
        return $this->update($id, ['active' => 0]);
    }
    
    public function count($conditions = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $this->count($conditions);
        $totalPages = ceil($total / $perPage);
        
        // Get data
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }
        
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
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
}
?>