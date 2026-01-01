<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /homeplan/auth/register_architect.php");
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$certificate_number = trim($_POST['certificate_number'] ?? '');
$years_experience = (int)($_POST['years_experience'] ?? 0);
$expertise = trim($_POST['expertise'] ?? '');
$portfolio_url = trim($_POST['portfolio_url'] ?? '');

if ($full_name === '' || $email === '' || $password === '' || $certificate_number === '' || $expertise === '') {
    header("Location: /homeplan/auth/register_architect.php?err=missing");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: /homeplan/auth/register_architect.php?err=email");
        exit;
    }

    $stmt = $pdo->prepare("SELECT architect_id FROM architect_profiles WHERE certificate_number = ? LIMIT 1");
    $stmt->execute([$certificate_number]);
    if ($stmt->fetch()) {
        header("Location: /homeplan/auth/register_architect.php?err=cert");
        exit;
    }

    $pdo->beginTransaction();

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (role, full_name, email, phone, password_hash) VALUES ('architect', ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone !== '' ? $phone : null, $password_hash]);

    $new_user_id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO architect_profiles (architect_id, certificate_number, years_experience, expertise, portfolio_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$new_user_id, $certificate_number, $years_experience, $expertise, $portfolio_url !== '' ? $portfolio_url : null]);

    $pdo->commit();

    header("Location: /homeplan/auth/login.php?msg=registered");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header("Location: /homeplan/auth/register_architect.php?err=server");
    exit;
}

