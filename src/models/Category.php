<?php
require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    protected $primaryKey = 'category_id';

    public function findAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT category_id, name as category_name, description FROM {$this->table} ORDER BY name";
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
            throw new Exception("Error fetching categories");
        }
    }

    public function findById($id) {
        try {
            $sql = "SELECT category_id, name as category_name, description FROM {$this->table} WHERE category_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching category");
        }
    }

    public function findWithBookCount() {
        try {
            $sql = "SELECT c.category_id, c.name as category_name, c.description, COUNT(b.book_id) as book_count 
                    FROM {$this->table} c 
                    LEFT JOIN books b ON c.category_id = b.category_id 
                    GROUP BY c.category_id 
                    ORDER BY c.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching categories with book count");
        }
    }

    public function create($data) {
        // Map category_name to name for database
        if (isset($data['category_name'])) {
            $data['name'] = $data['category_name'];
            unset($data['category_name']);
        }
        return parent::create($data);
    }

    public function update($id, $data) {
        // Map category_name to name for database
        if (isset($data['category_name'])) {
            $data['name'] = $data['category_name'];
            unset($data['category_name']);
        }
        return parent::update($id, $data);
    }

    protected function validate($data, $id = null) {
        $errors = [];

        // Check for both 'name' and 'category_name'
        $name = $data['name'] ?? $data['category_name'] ?? null;

        if (empty($name) || trim($name) === '') {
            $errors['name'] = 'Category name is required';
        }

        if (!empty($name)) {
            $sql = "SELECT category_id FROM {$this->table} WHERE name = :name";
            if ($id !== null) {
                $sql .= " AND category_id != :id";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $name);
            if ($id !== null) {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $errors['name'] = 'Category name already exists';
            }
        }

        return $errors;
    }
}
