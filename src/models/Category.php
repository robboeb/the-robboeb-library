<?php
require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    protected $primaryKey = 'category_id';

    public function findWithBookCount() {
        try {
            $sql = "SELECT c.*, COUNT(b.book_id) as book_count 
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

    protected function validate($data, $id = null) {
        $errors = [];

        if (empty($data['name']) || trim($data['name']) === '') {
            $errors['name'] = 'Category name is required';
        }

        if (!empty($data['name'])) {
            $sql = "SELECT category_id FROM {$this->table} WHERE name = :name";
            if ($id !== null) {
                $sql .= " AND category_id != :id";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $data['name']);
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
