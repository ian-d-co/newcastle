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
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['discord_name'] = $user['discord_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        redirect('/index.php?page=home');
    }
    
    public function logout() {
        session_destroy();
        redirect('/index.php?page=login');
    }
    
    public function register($discordName, $name, $pin) {
        // Check if user exists
        $existing = $this->userModel->findByDiscordName($discordName);
        if ($existing) {
            throw new Exception('Discord name already registered');
        }
        
        // Create user
        $userId = $this->userModel->create($discordName, $name, $pin);
        
        // Auto-login
        $user = $this->userModel->findById($userId);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['discord_name'] = $user['discord_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return $userId;
    }
}
