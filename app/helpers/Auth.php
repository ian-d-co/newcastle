<?php
/**
 * Dickscord Fest 2026 - Newcastle Event Management System
 * Authentication Helper Class
 * 
 * Static helper class for authentication operations including:
 * - PIN hashing and verification using bcrypt
 * - User login and logout
 * - Session management
 * - Authentication checks
 * - CSRF token generation and verification
 */

class AuthHelper {
    /**
     * Hash a PIN using bcrypt
     * 
     * @param string $pin PIN to hash
     * @return string Hashed PIN
     */
    public static function hashPin($pin) {
        return password_hash($pin, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }
    
    /**
     * Verify a PIN against its hash
     * 
     * @param string $pin Plain text PIN
     * @param string $hash Hashed PIN
     * @return bool True if PIN is correct
     */
    public static function verifyPin($pin, $hash) {
        return password_verify($pin, $hash);
    }
    
    /**
     * Log in a user
     * 
     * @param int $userId User ID
     * @param bool $isAdmin Whether user is admin
     * @return bool True on success
     */
    public static function login($userId, $isAdmin = false) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = $isAdmin;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Log out the current user
     * 
     * @return bool True on success
     */
    public static function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public static function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive > SESSION_LIFETIME) {
                self::logout();
                return false;
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if current user is admin
     * 
     * @return bool True if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user from database
     * 
     * @return array|false User data or false if not found
     */
    public static function user() {
        $userId = self::userId();
        if (!$userId) {
            return false;
        }
        
        $sql = "SELECT id, discord_name, name, is_admin, created_at FROM users WHERE id = :id";
        return Database::fetchOne($sql, ['id' => $userId]);
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken() {
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
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token HTML input field
     * 
     * @return string HTML input field
     */
    public static function csrfField() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::generateCsrfToken() . '">';
    }
    
    /**
     * Require authentication (redirect to login if not authenticated)
     * 
     * @param string $redirectUrl URL to redirect to after login
     */
    public static function requireAuth($redirectUrl = null) {
        if (!self::isLoggedIn()) {
            if ($redirectUrl) {
                $_SESSION['redirect_after_login'] = $redirectUrl;
            }
            redirect('/index.php?page=login');
        }
    }
    
    /**
     * Require admin authentication (403 if not admin)
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            http_response_code(403);
            die('Access denied. Admin privileges required.');
        }
    }
    
    /**
     * Check CSRF token for POST requests
     * 
     * @throws Exception If CSRF token is invalid
     */
    public static function checkCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            
            if (!self::verifyCsrfToken($token)) {
                http_response_code(403);
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
                } else {
                    die('Invalid CSRF token. Please try again.');
                }
            }
        }
    }
}
