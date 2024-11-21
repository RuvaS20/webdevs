<?php
// utils/session_manager.php

class SessionManager {
    private $sessionTimeout = 1800; // 30 minutes
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSessionTimeout();
    }
    
    /**
     * Stores user data in session after successful login
     */
    public function login($userData) {
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * Destroys session and cookies on logout
     */
    public function logout() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Checks if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Gets current user's role
     */
    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }

  /**
     * Gets current usernmae
     */
    public function getUserRole() {
        return $_SESSION['username'] ?? null;
    }
    
    /**
     * Gets current user's ID
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Gets current user's email address
     */
    public function getUserEmail() {
        return $_SESSION['email'] ?? null;
    }
    
    /**
     * Checks session timeout and logs out if exceeded
     */
    private function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > $this->sessionTimeout) {
            $this->logout();
        }
        $_SESSION['last_activity'] = time();
    }
}
