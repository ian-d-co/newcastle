<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Helper Functions
 * 
 * This file contains global helper functions used throughout the application.
 */

/**
 * Initialize session with secure settings
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session settings before starting
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', SESSION_COOKIE_SAMESITE);
        
        if (SESSION_COOKIE_SECURE) {
            ini_set('session.cookie_secure', '1');
        }
        
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE
        ]);
        
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    // Check if session is started first
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log('WARNING: isLoggedIn() called but session not started');
        return false;
    }
    
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if current user is admin
 * 
 * @return bool True if user is admin
 */
function isAdmin() {
    // Check if session is started first
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log('WARNING: isAdmin() called but session not started');
        return false;
    }
    
    // Must be logged in first
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    if (headers_sent()) {
        echo "<script>window.location.href='$url';</script>";
    } else {
        header("Location: $url");
    }
    exit;
}

/**
 * Send JSON response
 * 
 * @param mixed $data Data to send
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Sanitize output for HTML display
 * 
 * @param string $str String to sanitize
 * @return string Sanitized string
 */
function escapeHtml($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Short alias for escapeHtml() function
 * 
 * @param string $str String to escape
 * @return string Escaped string
 */
function e($str) {
    return escapeHtml($str);
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date or empty string if invalid
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }
    return date($format, $timestamp);
}

/**
 * Format date as DD MONTH YEAR (e.g., 18 February 2026)
 * 
 * @param string $date Date string
 * @return string Formatted date or empty string if invalid
 */
function formatDisplayDate($date) {
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }
    return date('d F Y', $timestamp);
}

/**
 * Format time in 24-hour clock (e.g., 14:30)
 * 
 * @param string $time Time string
 * @return string Formatted time or empty string if invalid
 */
function formatDisplayTime($time) {
    $timestamp = strtotime($time);
    if ($timestamp === false) {
        return '';
    }
    return date('H:i', $timestamp);
}

/**
 * Get database connection using PDO
 * 
 * @return PDO Database connection instance
 * @throws PDOException If connection fails
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // Log connection attempt for debugging
            error_log(sprintf(
                'Attempting database connection - Host: %s, Port: %s, DB: %s, User: %s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_USER
            ));
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                // Set connection timeout for MySQL (in seconds)
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            
            // Test connection with a simple query
            $pdo->query('SELECT 1');
            
            error_log('Database connection successful');
        } catch (PDOException $e) {
            $errorMsg = 'Database connection failed: ' . $e->getMessage();
            error_log($errorMsg);
            error_log('DSN: mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);
            
            if (APP_DEBUG) {
                // In debug mode, show detailed error
                die('<html><body style="font-family: Arial; padding: 20px;"><h1>Database Connection Error</h1><p>' . 
                    htmlspecialchars($errorMsg) . '</p><p>Please check:</p><ul>' .
                    '<li>.env file exists in app/config/ or root directory</li>' .
                    '<li>Database credentials are correct</li>' .
                    '<li>Database server is running</li>' .
                    '<li>Database ' . htmlspecialchars(DB_NAME) . ' exists</li>' .
                    '</ul></body></html>');
            } else {
                // In production, show generic message
                die('<html><body style="font-family: Arial; padding: 20px;"><h1>Service Unavailable</h1>' .
                    '<p>The website is currently experiencing technical difficulties. ' .
                    'Please try again later or contact the administrator.</p></body></html>');
            }
        }
    }
    
    return $pdo;
}
