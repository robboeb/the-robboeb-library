<?php
require_once __DIR__ . '/Database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching records");
        }
    }

    public function findById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching record");
        }
    }

    public function create($data) {
        try {
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->db->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->execute();
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error creating record");
        }
    }

    public function update($id, $data) {
        try {
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            $setParts = [];
            foreach (array_keys($data) as $key) {
                $setParts[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'affected_rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error updating record");
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'affected_rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error deleting record");
        }
    }

    abstract protected function validate($data, $id = null);

    protected function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Model Error ({$this->table}): {$message}\n";
        error_log($logMessage, 3, LOG_PATH);
    }
}
