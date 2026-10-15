<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/database.php';
if (!isset($pdo) && isset($conn)) $pdo = $conn;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$category = $_POST['category'] ?? 'General';
$subject = $_POST['subject'] ?? $_POST['title'] ?? 'Test';
$description = $_POST['description'] ?? 'Test desc';

$stmt = $pdo->prepare("INSERT INTO ccms_complaints (user_id, category, subject, description, status) VALUES (?,?,?,?, 'Pending')");
$stmt->execute([$user_id, $category, $subject, $description]);

header("Location: dashboard.php?success=1");
exit();
?>