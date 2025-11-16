<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching user by email");
        }
    }

    public function authenticate($email, $password) {
        try {
            $user = $this->findByEmail($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                unset($user['password_hash']);
                return $user;
            }
            return false;
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    public function create($data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return parent::create($data);
    }

    public function updatePassword($userId, $newPassword) {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE {$this->table} SET password_hash = :password_hash WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':password_hash', $passwordHash);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error updating password");
        }
    }

    public function deactivate($userId) {
        try {
            $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error deactivating user");
        }
    }

    public function findByType($userType, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT user_id, email, first_name, last_name, phone, address, user_type, status, created_at, updated_at 
                    FROM {$this->table} WHERE user_type = :user_type";
            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_type', $userType);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_type', $userType);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Error fetching users by type");
        }
    }

    public function findById($id) {
        $user = parent::findById($id);
        if ($user) {
            unset($user['password_hash']);
        }
        return $user;
    }

    public function findAll($limit = null, $offset = 0) {
        $users = parent::findAll($limit, $offset);
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        return $users;
    }

    protected function validate($data, $id = null) {
        $errors = [];

        if (empty($data['email']) || trim($data['email']) === '') {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            $sql = "SELECT user_id FROM {$this->table} WHERE email = :email";
            if ($id !== null) {
                $sql .= " AND user_id != :id";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $data['email']);
            if ($id !== null) {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $errors['email'] = 'Email already exists';
            }
        }

        if (empty($data['first_name']) || trim($data['first_name']) === '') {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($data['last_name']) || trim($data['last_name']) === '') {
            $errors['last_name'] = 'Last name is required';
        }

        if (isset($data['user_type']) && !in_array($data['user_type'], ['admin', 'patron'])) {
            $errors['user_type'] = 'Invalid user type';
        }

        if ($id === null && (empty($data['password']) || strlen($data['password']) < 8)) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        return $errors;
    }
}
