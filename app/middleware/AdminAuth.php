<?php
class AdminAuth {
    public static function check() {
        if (!isLoggedIn()) {
            redirect('/index.php?page=login');
        }
        
        if (!isAdmin()) {
            redirect('/index.php?page=home');
        }
    }
}
