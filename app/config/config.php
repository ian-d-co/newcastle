<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Main Configuration File
 * 
 * This file contains all application configuration including:
 * - Database connection settings
 * - Session configuration
 * - Security settings
 * - Application paths
 * - Event-specific settings
 * - Color scheme
 */

// Load environment variables from .env file if it exists
// Look for .env in app/config directory first, then fall back to root
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../../.env';
}

$envFileLoaded = false;
if (file_exists($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Set environment variable if not already set
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        $envFileLoaded = true;
    } else {
        error_log('Warning: .env file exists but could not be read: ' . $envFile);
    }
} else {
    error_log('Warning: .env file not found. Checked: ' . __DIR__ . '/.env and ' . __DIR__ . '/../../.env');
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'u983097270_newc');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('TIMEZONE', getenv('TIMEZONE') ?: 'Europe/London');

// Session Configuration
define('SESSION_NAME', getenv('SESSION_NAME') ?: 'dicksord_fest_2026');
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 3600)); // 1 hour default
define('SESSION_COOKIE_SECURE', APP_ENV === 'production'); // Only HTTPS in production
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Lax');

// Security Configuration
define('BCRYPT_COST', 10); // Password hashing cost
define('CSRF_TOKEN_NAME', getenv('CSRF_TOKEN_NAME') ?: 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

// Application Paths
define('APP_ROOT', dirname(dirname(__DIR__)));
define('APP_PATH', APP_ROOT . '/app');
define('PUBLIC_PATH', APP_ROOT . '/public');
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');

// Event Configuration
define('EVENT_ID', 1);
define('EVENT_NAME', 'Dicksord Fest 2026 - Newcastle');
define('EVENT_START_DATE', '2026-11-20');
define('EVENT_END_DATE', '2026-11-22');
define('EVENT_LOCATION', 'Newcastle');

// Color Scheme
define('COLOR_PRIMARY', '#613704');      // Dark Brown
define('COLOR_SECONDARY', '#D46300');    // Orange
define('COLOR_ACCENT', '#FDDC62');       // Gold
define('COLOR_LIGHT', '#FDE5B7');        // Light Cream
define('COLOR_WHITE', '#FFFFFF');        // White
define('COLOR_GRAY', '#545454');         // Gray
define('COLOR_BLACK', '#000000');        // Black

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    // Set error log location if not already set
    if (!ini_get('error_log')) {
        ini_set('error_log', __DIR__ . '/../../logs/error.log');
    }
}

// Custom error handler to prevent blank pages
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    
    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>Error [$errno]:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    // Don't execute PHP internal error handler
    return true;
});

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
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Format date as DD MONTH YEAR (e.g., 18 February 2026)
 * 
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDisplayDate($date) {
    return date('d F Y', strtotime($date));
}

/**
 * Format time in 24-hour clock (e.g., 14:30)
 * 
 * @param string $time Time string
 * @return string Formatted time
 */
function formatDisplayTime($time) {
    return date('H:i', strtotime($time));
}
