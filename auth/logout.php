<?php
// /homeplan/auth/logout.php

require_once __DIR__ . '/../config/session.php';

// Unset all session variables
$_SESSION = [];

// If session cookie exists, remove it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    // Clear default session cookie name (usually PHPSESSID)
    setcookie(session_name(), '', time() - 42000, $params['path'] ?: '/', $params['domain'] ?? '', false, true);

    // ALSO clear any old custom cookie name that may exist from previous tests
    setcookie('homeplan_sess', '', time() - 42000, '/', '', false, true);
}

// Destroy session
session_destroy();

// Redirect to login
header("Location: /homeplan/auth/login.php");
exit;

