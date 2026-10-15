<?php
session_start();
require_once __DIR__. '/config/database.php';
if (!isset($pdo) && isset($conn)) $pdo = $conn;
$msg="";
if($_SERVER['REQUEST_METHOD']=='POST'){
 $e=$_POST['email']??''; $p=$_POST['password']??'';
 $s=$pdo->prepare("SELECT * FROM ccms_users WHERE email=? LIMIT 1"); $s->execute([$e]); $u=$s->fetch();
 if($u && ($p==$u['password'] || password_verify($p,$u['password']))){
  $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['name']??explode('@',$u['email'])[0]; $_SESSION['user_email']=$u['email']; $_SESSION['role']=$u['role']??'student';
  header("Location: dashboard.php"); exit();
 }else{ $msg="Invalid Email or Password!"; }
}
?>
<!DOCTYPE html><html><head><title>CCMS Login</title><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{margin:0;min-height:100vh;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;font-family:Segoe UI}
.card{width:900px;max-width:95%;background:#fff;border-radius:20px;overflow:hidden;display:flex;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.left{flex:1;background:linear-gradient(135deg,#4e54c8,#8f94fb);color:#fff;padding:45px;display:flex;flex-direction:column;justify-content:center}
.right{flex:1;padding:45px}
.form-control{padding:12px;border-radius:10px;background:#f8f9ff}
.btn-login{width:100%;padding:12px;border-radius:10px;border:0;background:linear-gradient(90deg,#4e54c8,#8f94fb);color:#fff;font-weight:600}
@media(max-width:768px){.card{flex-direction:column}}
</style></head><body>
<div class="card">
<div class="left"><h1>🔐</h1><h3 class="fw-bold mt-3">College Complaint<br>Management System</h3><p class="mt-3 small opacity-75">Securely submit grievances. Track status in real-time. Get admin replies within 24 hours.</p><div class="mt-4 small">✅ Fast & Secure<br>⚡ Instant Tracking<br>💬 Direct Admin Response</div></div>
<div class="right"><h4 class="fw-bold">Welcome Back! 👋</h4><p class="text-muted small mb-4">Enter your email to continue</p>
<?php if($msg):?><div class="alert alert-danger py-2 small"><?=$msg?></div><?php endif;?>
<form method="POST"><label class="small fw-bold">Email Address</label><input type="email" name="email" class="form-control mb-3" placeholder="Enter your email address" required><label class="small fw-bold">Password</label><input type="password" name="password" class="form-control mb-3" placeholder="Enter your password" required><button class="btn-login">Login →</button></form>
<div class="text-center mt-3"><small class="text-muted">Demo: student@gmail.com / 123456<br><a href="register.php" class="fw-bold text-decoration-none">Register</a></small></div></div>
</div></body></html>