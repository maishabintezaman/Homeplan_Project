<?php
// /homeplan/config/session.php

// Start session safely once, with cookie settings that work on localhost + XAMPP.
// This prevents "login works then session becomes empty".

if (session_status() === PHP_SESSION_NONE) {

    // IMPORTANT: keep cookie valid for entire site
    $cookieParams = session_get_cookie_params();

    // PHP 7.3+ supports array format
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',       // VERY IMPORTANT
        'domain'   => '',        // keep default host
        'secure'   => false,     // localhost http
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Optional but helpful
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}
