<?php
require_once __DIR__ . '/BaseModel.php';

class Book extends BaseModel {
    protected $table = 'books';
    protected $primaryKey = 'book_id';

    public function findByISBN($isbn) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE isbn = :isbn";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':isbn', $isbn);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching book by ISBN");
        }
    }

    public function findByCategory($categoryId, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE category_id = :category_id";
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching books by category");
        }
    }

    public function search($query, $limit = null, $offset = 0) {
        try {
            $searchTerm = "%{$query}%";
            $sql = "SELECT DISTINCT b.* FROM {$this->table} b 
                    LEFT JOIN book_authors ba ON b.book_id = ba.book_id 
                    LEFT JOIN authors a ON ba.author_id = a.author_id 
                    WHERE b.title LIKE :query1 
                    OR b.isbn LIKE :query2 
                    OR CONCAT(a.first_name, ' ', a.last_name) LIKE :query3";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':query1', $searchTerm);
                $stmt->bindValue(':query2', $searchTerm);
                $stmt->bindValue(':query3', $searchTerm);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':query1', $searchTerm);
                $stmt->bindValue(':query2', $searchTerm);
                $stmt->bindValue(':query3', $searchTerm);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error searching books");
        }
    }

    public function updateQuantity($bookId, $change) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET available_quantity = available_quantity + :change 
                    WHERE book_id = :book_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':change', $change, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error updating book quantity");
        }
    }

    public function addAuthors($bookId, $authorIds) {
        try {
            $sql = "INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($authorIds as $authorId) {
                $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
                $stmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
                $stmt->execute();
            }
            return ['success' => true];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error adding authors to book");
        }
    }

    public function removeAuthors($bookId) {
        try {
            $sql = "DELETE FROM book_authors WHERE book_id = :book_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error removing authors from book");
        }
    }

    public function getAuthors($bookId) {
        try {
            $sql = "SELECT a.* FROM authors a 
                    INNER JOIN book_authors ba ON a.author_id = ba.author_id 
                    WHERE ba.book_id = :book_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching book authors");
        }
    }

    protected function validate($data, $id = null) {
        $errors = [];

        if (empty($data['isbn']) || trim($data['isbn']) === '') {
            $errors['isbn'] = 'ISBN is required';
        } elseif (!preg_match('/^\d{10}(\d{3})?$/', $data['isbn'])) {
            $errors['isbn'] = 'ISBN must be 10 or 13 digits';
        } else {
            $sql = "SELECT book_id FROM {$this->table} WHERE isbn = :isbn";
            if ($id !== null) {
                $sql .= " AND book_id != :id";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':isbn', $data['isbn']);
            if ($id !== null) {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $errors['isbn'] = 'ISBN already exists';
            }
        }

        if (empty($data['title']) || trim($data['title']) === '') {
            $errors['title'] = 'Title is required';
        }

        if (isset($data['total_quantity']) && $data['total_quantity'] < 0) {
            $errors['total_quantity'] = 'Total quantity cannot be negative';
        }

        if (isset($data['available_quantity']) && $data['available_quantity'] < 0) {
            $errors['available_quantity'] = 'Available quantity cannot be negative';
        }

        return $errors;
    }
}
