<?php
session_start();
require_once __DIR__ . '/config/database.php';
if (!isset($pdo) && isset($conn)) $pdo = $conn;
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$list = $pdo->prepare("SELECT * FROM ccms_complaints WHERE user_id=? ORDER BY id DESC");
$list->execute([$user_id]); $complaints = $list->fetchAll();
?>
<!DOCTYPE html>
<html><head><title>My Complaints</title><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>body{background:#f5f7fb} .card{border:0;box-shadow:0 4px 15px rgba(0,0,0,.07);border-radius:14px}</style>
</head><body>
<nav class="navbar navbar-dark" style="background:linear-gradient(90deg,#4e54c8,#8f94fb)"><div class="container"><a href="dashboard.php" class="text-white text-decoration-none fw-bold"><i class="bi bi-arrow-left"></i> Back to Dashboard</a><span class="text-white">My Complaints (<?= count($complaints) ?>)</span></div></nav>
<div class="container mt-4">
<?php foreach($complaints as $c): 
$color = $c['status']=='Pending'?'warning':'success';
?>
<div class="card p-4 mb-3">
<div class="d-flex justify-content-between align-items-start">
<div><h5 class="fw-bold mb-1"><?= htmlspecialchars($c['subject']) ?></h5><span class="badge bg-light text-dark border"><i class="bi bi-tag"></i> <?= htmlspecialchars($c['category']) ?></span> <small class="text-muted ms-2">#<?= $c['id'] ?></small></div>
<span class="badge bg-<?= $color ?>"><?= $c['status'] ?></span>
</div>
<p class="mt-3 text-muted"><?= nl2br(htmlspecialchars($c['description'])) ?></p>
<div class="bg-primary bg-opacity-10 p-3 rounded"><i class="bi bi-reply-fill text-primary"></i> <b>Admin Reply:</b><br><?= htmlspecialchars($c['admin_reply'] ?? 'Waiting for admin reply... You will get reply within 24 hours.') ?></div>
</div>
<?php endforeach; ?>
<?php if(empty($complaints)) echo '<div class="card p-5 text-center"><h5>No complaints yet</h5><a href="dashboard.php" class="btn btn-primary mt-2">Submit First Complaint</a></div>'; ?>
</div></body></html>