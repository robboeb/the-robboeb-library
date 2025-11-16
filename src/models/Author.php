<?php
require_once __DIR__ . '/BaseModel.php';

class Author extends BaseModel {
    protected $table = 'authors';
    protected $primaryKey = 'author_id';

    public function findWithBooks($authorId) {
        try {
            $sql = "SELECT a.*, b.book_id, b.title, b.isbn 
                    FROM {$this->table} a 
                    LEFT JOIN book_authors ba ON a.author_id = ba.author_id 
                    LEFT JOIN books b ON ba.book_id = b.book_id 
                    WHERE a.author_id = :author_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching author with books");
        }
    }

    protected function validate($data, $id = null) {
        $errors = [];

        if (empty($data['first_name']) || trim($data['first_name']) === '') {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($data['last_name']) || trim($data['last_name']) === '') {
            $errors['last_name'] = 'Last name is required';
        }

        return $errors;
    }
}
