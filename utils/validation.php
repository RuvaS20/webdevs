<?php
/**
 * Validation functions for authentication and user data
 */

class Validate {
    /**
     * Error messages storage
     */
    private $errors = [];

    /**
     * Get all validation errors
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Validate email address
     * @param string $email
     * @return bool
     */
    public function email(string $email): bool {
        $email = trim($email);
        
        if (empty($email)) {
            $this->errors['email'] = 'Email address is required';
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
            return false;
        }
        
        // Check email length
        if (strlen($email) > 100) {
            $this->errors['email'] = 'Email address must be less than 100 characters';
            return false;
        }
        
        return true;
    }

    /**
     * Validate username
     * @param string $username
     * @return bool
     */
    public function username(string $username): bool {
        $username = trim($username);
        
        if (empty($username)) {
            $this->errors['username'] = 'Username is required';
            return false;
        }
        
        // Check length (3-50 characters)
        if (strlen($username) < 3 || strlen($username) > 50) {
            $this->errors['username'] = 'Username must be between 3 and 50 characters';
            return false;
        }
        
        // Allow only alphanumeric characters and underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->errors['username'] = 'Username can only contain letters, numbers, and underscores';
            return false;
        }
        
        return true;
    }

    /**
     * Validate password
     * @param string $password
     * @return bool
     */
    public function password(string $password): bool {
        if (empty($password)) {
            $this->errors['password'] = 'Password is required';
            return false;
        }
        
        // Check length (min 8 characters)
        if (strlen($password) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters long';
            return false;
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one uppercase letter';
            return false;
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one lowercase letter';
            return false;
        }
        
        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one number';
            return false;
        }
        
        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one special character';
            return false;
        }
        
        return true;
    }

    /**
     * Validate password confirmation
     * @param string $password
     * @param string $confirmPassword
     * @return bool
     */
    public function passwordConfirmation(string $password, string $confirmPassword): bool {
        if ($password !== $confirmPassword) {
            $this->errors['confirm_password'] = 'Passwords do not match';
            return false;
        }
        
        return true;
    }

    /**
     * Sanitize input data
     * @param string $data
     * @return string
     */
    public function sanitize(string $data): string {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Check if all validations passed
     * @return bool
     */
    public function passes(): bool {
        return empty($this->errors);
    }
}

?>
