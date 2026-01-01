<?php
require_once __DIR__ . '/config/session.php';

echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "_COOKIE:\n";
print_r($_COOKIE);

echo "\n_SESSION:\n";
print_r($_SESSION);

echo "\nSession save path: " . session_save_path() . "\n";
echo "Writable save path? " . (is_writable(session_save_path()) ? "YES" : "NO") . "\n";
echo "</pre>";
