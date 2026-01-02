<?php
// /homeplan/client/request_property.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: /homeplan/auth/login.php");
    exit;
}

$client_id = (int)$_SESSION['user_id'];

// POST is expected (because we submit a form)
$property_id = (int)($_POST['property_id'] ?? 0);
if ($property_id <= 0) {
    header("Location: /homeplan/client/properties.php?msg=invalid");
    exit;
}

/* ---------- Get property + provider_id + availability ---------- */
$stmt = $conn->prepare("SELECT provider_id, availability_status, project_name FROM properties WHERE property_id = ? LIMIT 1");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prop) {
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=not_found");
    exit;
}

$provider_id  = (int)($prop['provider_id'] ?? 0);
$statusAvail  = (string)($prop['availability_status'] ?? '');
$project_name = trim((string)($prop['project_name'] ?? ''));

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
    /* ---------- Prevent duplicate property requests ---------- */
    $chk = $conn->prepare("
        SELECT request_id
        FROM requests
        WHERE client_id = ? AND property_id = ? AND request_type = 'property'
        LIMIT 1
    ");
    $chk->bind_param("ii", $client_id, $property_id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($existing) {
        $conn->commit();
        header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=already_requested");
        exit;
    }

    /* ---------- Insert request (store provider_id so receiver is known) ---------- */
    $stmt = $conn->prepare("
        INSERT INTO requests (client_id, provider_id, property_id, request_type)
        VALUES (?, ?, ?, 'property')
    ");
    $stmt->bind_param("iii", $client_id, $provider_id, $property_id);
    $stmt->execute();
    $stmt->close();

    $request_id = (int)$conn->insert_id;

    /* ---------- Create notification for the listing owner (developer OR property_owner) ---------- */
    $propLabel = ($project_name !== '') ? $project_name : ("Property #".$property_id);
    $msg = "New property request for '{$propLabel}' (Request ID #{$request_id})";

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, status, creation_date)
        VALUES (?, ?, 'unread', NOW())
    ");
    $stmt->bind_param("is", $provider_id, $msg);
    $stmt->execute();
    $stmt->close();

    /* ---------- Optional: keep request_assignments (safe even if unused) ---------- */
    $stmt = $conn->prepare("
        INSERT INTO request_assignments (request_id, provider_id, property_id)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $request_id, $provider_id, $property_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=requested");
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();

    // Duplicate request (if you later add UNIQUE constraint)
    if ((int)$e->getCode() === 1062) {
        header("Location: /homeplan/client/property_view.php?id={$property_id}&msg=already_requested");
        exit;
    }

    http_response_code(500);
    die("Request failed: " . $e->getMessage());
}

