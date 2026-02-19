<?php
class Auth {
    public static function check() {
        // Allow guest mode to browse authenticated routes
        if (isGuestMode()) {
            return;
        }

        if (!isLoggedIn()) {
            redirect('/index.php?page=login');
        }

        // Check if logged-in user is approved (use cached session value when available)
        if (!isset($_SESSION['is_approved'])) {
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT approved FROM users WHERE id = :id");
                $stmt->execute(['id' => getCurrentUserId()]);
                $user = $stmt->fetch();
                $_SESSION['is_approved'] = !$user || !isset($user['approved']) || (bool)$user['approved'];
            } catch (Exception $e) {
                // If approved column doesn't exist yet, skip the check
                error_log('Auth::check() approval check failed: ' . $e->getMessage());
                $_SESSION['is_approved'] = true;
            }
        }
        if (!$_SESSION['is_approved']) {
            redirect('/index.php?page=pending_approval');
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
