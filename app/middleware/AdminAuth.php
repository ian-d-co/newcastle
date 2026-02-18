<?php
class AdminAuth {
    public static function check() {
        error_log('AdminAuth::check() - Is logged in: ' . (isLoggedIn() ? 'YES' : 'NO'));
        error_log('AdminAuth::check() - Is admin: ' . (isAdmin() ? 'YES' : 'NO'));
        
        if (!isLoggedIn()) {
            error_log('AdminAuth::check() - Redirecting to login (not logged in)');
            redirect('/index.php?page=login');
        }
        
        if (!isAdmin()) {
            error_log('AdminAuth::check() - Redirecting to home (not admin)');
            error_log('AdminAuth::check() - Session is_admin value: ' . print_r($_SESSION['is_admin'] ?? 'NOT SET', true));
            redirect('/index.php?page=home');
        }
        
        error_log('AdminAuth::check() - Access granted');
    }
}
