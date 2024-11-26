<?php
class Validate {
    private $errors = [];
    public function getErrors(): array {
        return $this->errors;
    }

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
        
        if (strlen($email) > 100) {
            $this->errors['email'] = 'Email address must be less than 100 characters';
            return false;
        }
        
        return true;
    }

    public function username(string $username): bool {
        $username = trim($username);
        
        if (empty($username)) {
            $this->errors['username'] = 'Username is required';
            return false;
        }
        
        if (strlen($username) < 3 || strlen($username) > 50) {
            $this->errors['username'] = 'Username must be between 3 and 50 characters';
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->errors['username'] = 'Username can only contain letters, numbers, and underscores';
            return false;
        }
        
        return true;
    }

    public function password(string $password): bool {
        if (empty($password)) {
            $this->errors['password'] = 'Password is required';
            return false;
        }
        
        if (strlen($password) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters long';
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one uppercase letter';
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one lowercase letter';
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one number';
            return false;
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $this->errors['password'] = 'Password must contain at least one special character';
            return false;
        }
        
        return true;
    }

    public function passwordConfirmation(string $password, string $confirmPassword): bool {
        if ($password !== $confirmPassword) {
            $this->errors['confirm_password'] = 'Passwords do not match';
            return false;
        }
        
        return true;
    }

    public function sanitize(string $data): string {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    
    public function passes(): bool {
        return empty($this->errors);
    }
}

?>
