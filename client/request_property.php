<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /homeplan/client/properties.php");
    exit;
}

$client_id   = (int)$_SESSION['user_id'];
$property_id = (int)($_POST['property_id'] ?? 0);

if ($property_id <= 0) {
    header("Location: /homeplan/client/properties.php?msg=invalid");
    exit;
}

// Make sure this client exists in clients table (FK requirement)
$stmt = $conn->prepare("INSERT IGNORE INTO clients (client_id) VALUES (?)");
$stmt->bind_param("i", $client_id);
$stmt->execute();

// Check property + provider + availability
$stmt = $conn->prepare("SELECT provider_id, availability_status FROM properties WHERE property_id = ? LIMIT 1");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$res = $stmt->get_result();
$prop = $res->fetch_assoc();

if (!$prop) {
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=not_found");
    exit;
}

$provider_id = (int)$prop['provider_id'];
$statusAvail = $prop['availability_status'] ?? '';

if ($provider_id <= 0) {
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=not_found");
    exit;
}

if ($statusAvail !== 'available') {
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=not_available");
    exit;
}

$conn->begin_transaction();

try {
    // Insert request (status default = pending in DB)
    $stmt = $conn->prepare("INSERT INTO requests (client_id, property_id, request_type) VALUES (?, ?, 'property')");
    $stmt->bind_param("ii", $client_id, $property_id);
    $stmt->execute();

    $request_id = $conn->insert_id;

    // Insert assignment so provider can see and act
    $stmt = $conn->prepare("INSERT INTO request_assignments (request_id, provider_id, property_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $request_id, $provider_id, $property_id);
    $stmt->execute();

    $conn->commit();
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=requested");
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();

    // Duplicate request (unique constraint)
    if ((int)$e->getCode() === 1062) {
        header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=already_requested");
        exit;
    }

    http_response_code(500);
    die("Request failed: " . $e->getMessage());
}

