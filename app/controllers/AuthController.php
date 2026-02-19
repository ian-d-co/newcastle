<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin($error = null) {
        ob_start();
        include __DIR__ . '/../views/auth/login.php';
        return ob_get_clean();
    }
    
    public function login() {
        CSRF::validate();
        
        $discordName = $_POST['discord_name'] ?? '';
        $pin = $_POST['pin'] ?? '';
        
        if (empty($discordName) || empty($pin)) {
            return $this->showLogin('Please fill in all fields');
        }
        
        $user = $this->userModel->findByDiscordName($discordName);
        
        if (!$user || !$this->userModel->verifyPin($user, $pin)) {
            return $this->showLogin('Invalid Discord name or PIN');
        }

        // Check if user is approved (column may not exist on older schemas)
        if (isset($user['approved']) && !$user['approved']) {
            redirect('/index.php?page=pending_approval');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['discord_name'] = $user['discord_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        $_SESSION['is_approved'] = true; // Cache approval status to avoid per-request DB query
        
        // Clear guest mode on login
        unset($_SESSION['guest_mode']);
        
        // Log successful login
        error_log('=== LOGIN SUCCESSFUL ===');
        error_log('User ID: ' . $user['id']);
        error_log('Discord Name: ' . $user['discord_name']);
        error_log('Is Admin: ' . ($user['is_admin'] ? 'TRUE' : 'FALSE'));
        error_log('Session is_admin set to: ' . ($_SESSION['is_admin'] ? 'TRUE' : 'FALSE'));
        
        redirect('/index.php?page=home');
    }
    
    public function logout() {
        session_destroy();
        redirect('/index.php?page=home');
    }
    
    public function register($discordName, $name, $pin) {
        // Check if user exists
        $existing = $this->userModel->findByDiscordName($discordName);
        if ($existing) {
            throw new Exception('Discord name already registered');
        }
        
        // Create user (approved = 0 by default, requires admin approval)
        $userId = $this->userModel->create($discordName, $name, $pin);
        
        return $userId;
    }
}
