<?php
/**
 * Dickscord Fest 2026 - Newcastle Event Management System
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
define('PUBLIC_PATH', APP_ROOT);
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');

// Event Configuration
define('EVENT_ID', 1);
define('EVENT_NAME', 'Dickscord Fest 2026 - Newcastle');
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

// Helper functions are now defined in app/helpers/functions.php
// This keeps the config file focused on configuration only
