<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Bootstrap File
 * 
 * This file handles centralized path resolution, environment loading,
 * and configuration validation. It ensures the application works correctly
 * regardless of document root configuration.
 * 
 * It can be included from:
 * - public/index.php (Method 1: document root = public_html/public/)
 * - public_html/index.php (Method 2: document root = public_html/)
 * - public/api/*.php (API endpoints)
 */

// Prevent direct access
if (!defined('BOOTSTRAP_LOADED')) {
    define('BOOTSTRAP_LOADED', true);
}

// ============================================================================
// PATH RESOLUTION
// ============================================================================

/**
 * Determine the base path of the application
 * This works regardless of where the bootstrap file is included from
 */
if (!defined('BASE_PATH')) {
    // Get the directory containing app/bootstrap.php
    $bootstrapDir = __DIR__;
    
    // BASE_PATH is the parent of the 'app' directory
    define('BASE_PATH', dirname($bootstrapDir));
}

// Validate that BASE_PATH contains required directories
if (!is_dir(BASE_PATH . '/app') || !is_dir(BASE_PATH . '/public')) {
    error_log('FATAL: Invalid BASE_PATH. Required directories (app, public) not found at: ' . BASE_PATH);
    die('<html><body style="font-family: Arial; padding: 20px;"><h1>Configuration Error</h1>' .
        '<p>Application directory structure is invalid. Please check the installation.</p></body></html>');
}

// ============================================================================
// ENVIRONMENT LOADING
// ============================================================================

/**
 * Load .env file with proper error handling
 * Searches in multiple locations for maximum compatibility
 */
$envLocations = [
    BASE_PATH . '/app/config/.env',  // Preferred location
    BASE_PATH . '/.env',              // Root fallback
    BASE_PATH . '/public/.env',       // Legacy location (should not exist for security)
];

$envFileLoaded = false;
$envFilePath = null;

foreach ($envLocations as $location) {
    if (file_exists($location)) {
        $envFilePath = $location;
        
        // Warn if .env is in public directory (security risk)
        if (strpos($location, '/public/') !== false) {
            error_log('WARNING: .env file found in public directory. This is a security risk! Path: ' . $location);
        }
        
        $lines = @file($location, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                    
                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    // Set environment variable if not already set
                    if (!getenv($key)) {
                        putenv("$key=$value");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
            $envFileLoaded = true;
            error_log('Environment loaded from: ' . $location);
            break; // Stop after first successful load
        } else {
            error_log('Warning: .env file exists but could not be read: ' . $location);
        }
    }
}

// Log warning if no .env file was found
if (!$envFileLoaded) {
    error_log('Warning: No .env file found. Searched locations: ' . implode(', ', $envLocations));
    error_log('Application will use default configuration values.');
}

// ============================================================================
// CONFIGURATION CONSTANTS
// ============================================================================

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

// Application Paths (using BASE_PATH as foundation)
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');
define('LOGS_PATH', BASE_PATH . '/logs');

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

// ============================================================================
// PHP CONFIGURATION
// ============================================================================

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
    
    // Set error log location
    $logFile = LOGS_PATH . '/error.log';
    if (is_dir(LOGS_PATH) && is_writable(LOGS_PATH)) {
        ini_set('error_log', $logFile);
    }
}

// Custom error handler to prevent blank pages
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    
    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>Error [$errno]:</strong> " . htmlspecialchars($errstr) . "<br>";
        echo "<strong>File:</strong> " . htmlspecialchars($errfile) . "<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    // Don't execute PHP internal error handler
    return true;
});

// ============================================================================
// VALIDATION
// ============================================================================

// Log configuration summary
error_log('=== Bootstrap Configuration ===');
error_log('BASE_PATH: ' . BASE_PATH);
error_log('APP_ENV: ' . APP_ENV);
error_log('APP_DEBUG: ' . (APP_DEBUG ? 'true' : 'false'));
error_log('DB_HOST: ' . DB_HOST);
error_log('DB_NAME: ' . DB_NAME);
error_log('.env loaded: ' . ($envFileLoaded ? 'yes' : 'no'));
if ($envFilePath) {
    error_log('.env location: ' . $envFilePath);
}

// Verify critical paths exist
$criticalPaths = [
    'app/config' => CONFIG_PATH,
    'app/views' => VIEWS_PATH,
    'public' => PUBLIC_PATH,
];

foreach ($criticalPaths as $name => $path) {
    if (!is_dir($path)) {
        error_log("WARNING: Critical directory missing: $name at $path");
    }
}

// Ensure logs directory exists and is writable
if (!is_dir(LOGS_PATH)) {
    @mkdir(LOGS_PATH, 0755, true);
}

if (!is_writable(LOGS_PATH)) {
    error_log('WARNING: Logs directory is not writable: ' . LOGS_PATH);
}

// ============================================================================
// LOAD CORE CONFIGURATION
// ============================================================================

// Load the main config file which contains helper functions
$configFile = CONFIG_PATH . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    error_log('FATAL: Configuration file not found: ' . $configFile);
    die('<html><body style="font-family: Arial; padding: 20px;"><h1>Configuration Error</h1>' .
        '<p>Core configuration file is missing. Please check the installation.</p></body></html>');
}
