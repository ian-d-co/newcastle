<?php
class CSRF {
    public static function validate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            
            if (!verifyCsrfToken($token)) {
                jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            }
        }
    }
    
    public static function field() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
    }
    
    public static function token() {
        return generateCsrfToken();
    }
}
