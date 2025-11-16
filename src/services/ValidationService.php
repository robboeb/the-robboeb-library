<?php
class ValidationService {
    
    public static function validateEmail($email) {
        if (empty($email)) {
            return ['valid' => false, 'message' => 'Email is required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        return ['valid' => true];
    }

    public static function validateISBN($isbn) {
        if (empty($isbn)) {
            return ['valid' => false, 'message' => 'ISBN is required'];
        }
        
        $isbn = preg_replace('/[^0-9]/', '', $isbn);
        
        if (!preg_match('/^\d{10}(\d{3})?$/', $isbn)) {
            return ['valid' => false, 'message' => 'ISBN must be 10 or 13 digits'];
        }
        
        return ['valid' => true];
    }

    public static function validatePhone($phone) {
        if (empty($phone)) {
            return ['valid' => true];
        }
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return ['valid' => false, 'message' => 'Phone number must be between 10 and 15 digits'];
        }
        
        return ['valid' => true];
    }

    public static function validateRequired($value, $fieldName = 'Field') {
        if (empty($value) || trim($value) === '') {
            return ['valid' => false, 'message' => "{$fieldName} is required"];
        }
        
        return ['valid' => true];
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateLength($value, $min = null, $max = null, $fieldName = 'Field') {
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            return ['valid' => false, 'message' => "{$fieldName} must be at least {$min} characters"];
        }
        
        if ($max !== null && $length > $max) {
            return ['valid' => false, 'message' => "{$fieldName} must not exceed {$max} characters"];
        }
        
        return ['valid' => true];
    }

    public static function validateNumeric($value, $fieldName = 'Field') {
        if (!is_numeric($value)) {
            return ['valid' => false, 'message' => "{$fieldName} must be a number"];
        }
        
        return ['valid' => true];
    }

    public static function validateDate($date, $format = 'Y-m-d', $fieldName = 'Date') {
        $d = DateTime::createFromFormat($format, $date);
        
        if (!$d || $d->format($format) !== $date) {
            return ['valid' => false, 'message' => "{$fieldName} is not a valid date"];
        }
        
        return ['valid' => true];
    }
}
