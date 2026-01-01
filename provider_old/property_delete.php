<?php
session_start();

require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'provider') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /homeplan/provider/properties.php");
    exit;
}

$provider_id = (int)$_SESSION['user_id'];
$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;

if ($property_id <= 0) {
    header("Location: /homeplan/provider/properties.php");
    exit;
}

// Delete only provider's property
$stmt = $pdo->prepare("DELETE FROM properties WHERE property_id = ? AND provider_id = ?");
$stmt->execute([$property_id, $provider_id]);

header("Location: /homeplan/provider/properties.php?msg=deleted");
exit;
