<?php
header('Content-Type: text/plain; charset=utf-8');

$pass = $_GET['p'] ?? '1234';
echo "Password: " . $pass . "\n";
echo "Hash:\n";
echo password_hash($pass, PASSWORD_DEFAULT);
