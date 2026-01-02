<?php
// /homeplan/config/session.php

// One place to start session with correct cookie params for whole site.
// Fixes "login works then redirects back to login" due to cookie path issues.

if (session_status() === PHP_SESSION_NONE) {

    // Always set cookie valid for entire project
    // Use backward-compatible signature (works on old PHP too)
    $lifetime = 0;
    $path     = '/';   // IMPORTANT: whole site
    $domain   = '';    // localhost
    $secure   = false; // http on localhost
    $httponly = true;

    // PHP < 7.3 doesn't support samesite in cookie params.
    // So set normal params first:
    session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

    // Strict + cookie-only
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}
