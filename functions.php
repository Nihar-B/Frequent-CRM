<?php
// functions.php

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure Session Settings
        ini_set('session.cookie_httponly', 1); // Prevent JS access to session cookie
        ini_set('session.use_only_cookies', 1); // Force cookies
        // ini_set('session.cookie_secure', 1); // UNCOMMENT THIS IF USING HTTPS
        
        session_start();
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF Validation Failed. Please refresh the page.');
    }
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}
?>