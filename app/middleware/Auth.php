<?php
class Auth {
    public static function check() {
        if (!isLoggedIn()) {
            redirect('/index.php?page=login');
        }
    }
    
    public static function user() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function logout() {
        session_destroy();
        redirect('/index.php?page=login');
    }
}
