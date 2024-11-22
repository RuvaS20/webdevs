<?php
// utils/error_config.php

// Enable error reporting in development, disable in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Custom error handler class
class ErrorHandler {
    // Log levels
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    
    // Error types mapping
    private static $errorTypes = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];

    // Directory for log files
    private static $logDirectory = __DIR__ . '/../logs/';
    
    // Initialize error handling
    public static function initialize() {
        // Create logs directory if it doesn't exist
        if (!file_exists(self::$logDirectory)) {
            mkdir(self::$logDirectory, 0777, true);
        }
        
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
    }
    
    // Handle regular errors
    public static function handleError($errno, $errstr, $errfile, $errline) {
        $errorType = self::$errorTypes[$errno] ?? 'Unknown Error';
        $message = "$errorType: $errstr in $errfile on line $errline";
        
        self::logError($message, self::ERROR);
        
        // Display user-friendly error message in production
        if (!self::isDebugMode()) {
            self::displayUserFriendlyError();
            return true;
        }
        
        return false; // Let PHP handle the error in debug mode
    }
    
    // Handle uncaught exceptions
    public static function handleException($exception) {
        $message = "Uncaught Exception: " . $exception->getMessage() . 
                  "\nStack trace: " . $exception->getTraceAsString();
        
        self::logError($message, self::ERROR);
        
        if (!self::isDebugMode()) {
            self::displayUserFriendlyError();
        } else {
            echo "<pre>$message</pre>";
        }
    }
    
    // Log error to file
    private static function logError($message, $level) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        $logFile = self::$logDirectory . date('Y-m-d') . '_error.log';
        
        error_log($logMessage, 3, $logFile);
    }
    
    // Check if in debug mode
    private static function isDebugMode() {
        return defined('DEBUG_MODE') && DEBUG_MODE === true;
    }
    
    // Display user-friendly error message
    private static function displayUserFriendlyError() {
        http_response_code(500);
        include __DIR__ . '/../view/errors/500.php';
    }
    
    // Custom method for logging application-specific errors
    public static function logCustomError($message, $level = self::ERROR) {
        self::logError($message, $level);
    }
}

// Initialize error handling
ErrorHandler::initialize();
