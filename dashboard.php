<?php
session_start();
require_once __DIR__ . '/config/database.php';
$conn = $conn ?? $pdo ?? null;
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$role = strtolower($_SESSION['role'] ?? '');

if (isset($_POST['update_status']) && $role === 'admin') {
    $stmt = $conn->prepare("UPDATE ccms_complaints SET status=? WHERE id=?");
    $stmt->execute([$_POST['status'], $_POST['complaint_id']]);
    header("Location: dashboard.php?view=".$_POST['complaint_id']."&done=1");
    exit;
}
if (isset($_POST['submit_complaint']) && $role !== 'admin') {
    $stmt = $conn->prepare("INSERT INTO ccms_complaints (user_id, subject, description, category, status) VALUES (?,?,?,?, 'Pending')");
    $stmt->execute([$_SESSION['user_id'], $_POST['subject'], $_POST['description'], $_POST['category'] ?? 'General']);
    header("Location: dashboard.php?submitted=1");
    exit;
}

if($role === 'admin'){
    $total = $conn->query("SELECT COUNT(*) FROM ccms_complaints")->fetchColumn();
    $pending = $conn->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Pending'")->fetchColumn();
    $inprogress = $conn->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='In Progress'")->fetchColumn();
    $resolved = $conn->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Resolved'")->fetchColumn();
    $all = $conn->query("SELECT * FROM ccms_complaints ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $view = null;
    if(isset($_GET['view'])){ $s = $conn->prepare("SELECT * FROM ccms_complaints WHERE id=?"); $s->execute([$_GET['view']]); $view = $s->fetch(PDO::FETCH_ASSOC); }
} else {
    $my = $conn->prepare("SELECT * FROM ccms_complaints WHERE user_id=? ORDER BY id DESC"); $my->execute([$_SESSION['user_id']]); $my = $my->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"><style>
*{font-family:'Poppins',sans-serif} body{margin:0;background:#f5f7fb;color:#2d3748}
.header{background:linear-gradient(90deg,#6c4ab6,#8a6de8);color:white;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 12px rgba(108,74,182,.3)}
.header span{font-weight:600;font-size:18px;letter-spacing:.5px}
.logout{background:white;color:#6c4ab6;padding:6px 14px;border-radius:20px;text-decoration:none;font-weight:600;font-size:13px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;padding:20px}
.stat{background:white;border-radius:16px;padding:16px;text-align:center;box-shadow:0 6px 18px rgba(0,0,0,.06);border-top:4px solid #ddd}
.stat:nth-child(1){border-color:#6c4ab6} .stat:nth-child(2){border-color:#f6ad55} .stat:nth-child(3){border-color:#4fd1c5} .stat:nth-child(4){border-color:#68d391}
.stat b{font-size:28px;display:block;color:#1a202c} .stat small{color:#718096;font-size:13px;font-weight:500}
.main{padding:0 20px 20px}
.card{background:white;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06);overflow:hidden}
.table-head{padding:16px 20px;border-bottom:1px solid #edf2f7;display:flex;justify-content:space-between;align-items:center}
table{width:100%;border-collapse:collapse} th{padding:14px 18px;text-align:left;font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:.8px;background:#fafbff}
td{padding:14px 18px;border-top:1px solid #f1f5f9;font-size:14px}
.badge{padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.3px}
.pending{background:#fef3c7;color:#d97706} .inprogress{background:#e0f2fe;color:#0284c7} .resolved{background:#dcfce7;color:#16a34a}
.viewbtn{background:#6c4ab6;color:white;border:none;padding:7px 14px;border-radius:8px;font-size:12px;cursor:pointer;transition:.2s} .viewbtn:hover{background:#5a3ca0}
.detail{margin-top:18px;background:white;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.08);padding:20px;border-left:5px solid #6c4ab6;animation:slide .3s ease}
@keyframes slide{from{transform:translateY(10px);opacity:0}to{transform:translateY(0);opacity:1}}
.detail h3{margin:0 0 8px;color:#1a202c}
select,input,textarea{width:100%;padding:12px;margin:8px 0;border:1.5px solid #e2e8f0;border-radius:10px;box-sizing:border-box;background:#f8fafc;transition:.2s} select:focus,input:focus,textarea:focus{border-color:#6c4ab6;outline:none;background:white}
.update{background:linear-gradient(90deg,#22c55e,#16a34a);color:white;padding:12px;width:100%;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;box-shadow:0 4px 12px rgba(34,197,94,.3)}
.success{background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-weight:500}
</style></head><body>
<div class="header"><span>✦ CCMS - <?= $role==='admin'?'ADMIN PANEL':'STUDENT' ?></span><a href="logout.php" class="logout">Logout</a></div>

<?php if($role==='admin'): ?>
<div class="stats">
<div class="stat"><b><?= $total ?></b><small>📊 Total Complaints</small></div>
<div class="stat"><b><?= $pending ?></b><small>⏳ Pending</small></div>
<div class="stat"><b><?= $inprogress ?></b><small>🔄 In Progress</small></div>
<div class="stat"><b><?= $resolved ?></b><small>✅ Resolved</small></div>
</div>
<div class="main">
<?php if(isset($_GET['done'])) echo "<div class='success'>✅ Status Updated Successfully da!</div>"; ?>
<div class="card">
<div class="table-head"><b style="font-size:15px">All Complaints</b><small style="color:#718096"><?= $total ?> records</small></div>
<table><tr><th>ID</th><th>Subject</th><th>Status</th><th>Action</th></tr>
<?php foreach($all as $c): ?>
<tr><td style="font-weight:600;color:#6c4ab6">#<?= $c['id'] ?></td><td><?= htmlspecialchars($c['subject']) ?></td><td><span class="badge <?= strtolower(str_replace(' ','',$c['status'])) ?>"><?= $c['status'] ?></span></td><td><a href="?view=<?= $c['id'] ?>"><button class="viewbtn">View →</button></a></td></tr>
<?php endforeach; ?>
</table></div>

<?php if($view): ?>
<div class="detail">
<h3>#<?= $view['id'] ?> • <?= htmlspecialchars($view['subject']) ?></h3>
<p style="color:#4a5568;font-size:13px">Category: <b><?= $view['category'] ?? 'General' ?></b> | Priority: <?= $view['priority'] ?? 'Normal' ?></p>
<p style="background:#f8fafc;padding:12px;border-radius:10px;font-size:14px;color:#2d3748"><?= nl2br(htmlspecialchars($view['description'])) ?></p>
<form method="POST"><input type="hidden" name="complaint_id" value="<?= $view['id'] ?>">
<label style="font-size:12px;font-weight:600;color:#4a5568">UPDATE STATUS</label>
<select name="status"><option value="Pending" <?= $view['status']=='Pending'?'selected':'' ?>>⏳ Pending</option><option value="In Progress" <?= $view['status']=='In Progress'?'selected':'' ?>>🔄 In Progress</option><option value="Resolved" <?= $view['status']=='Resolved'?'selected':'' ?>>✅ Resolved</option></select>
<button name="update_status" class="update">UPDATE & RESOLVE</button>
</form>
<div style="text-align:center;margin-top:10px"><a href="dashboard.php" style="font-size:12px;color:#718096;text-decoration:none">✕ Close</a></div>
</div>
<?php endif; ?>
</div>

<?php else: ?>
<div class="main" style="padding-top:20px">
<?php if(isset($_GET['submitted'])) echo "<div class='success'>🎉 Complaint Submitted Successfully!</div>"; ?>
<div class="card" style="padding:20px;margin-bottom:16px"><h3 style="margin-top:0">📝 Submit New Complaint</h3>
<form method="POST"><input name="subject" placeholder="Ex: Fees not updated" required><select name="category"><option>Hostel</option><option>Fees</option><option>Maintenance</option><option>Food</option><option>Transport</option><option>Other</option></select><textarea name="description" rows="4" placeholder="Enter the full details..." required></textarea><button name="submit_complaint" style="background:linear-gradient(90deg,#6c4ab6,#8a6de8);color:white;width:100%;padding:12px;border-radius:10px">Submit Complaint 🚀</button></form></div>
<div class="card" style="padding:20px"><h3 style="margin-top:0">📋 My Complaints</h3><?php if(empty($my)) echo "<p style='color:#a0aec0;text-align:center;padding:20px'>No complaints yet</p>"; foreach($my as $c) echo "<div style='display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9'><span>#{$c['id']} {$c['subject']}</span><span class='badge ".strtolower(str_replace(' ','',$c['status']))."'>{$c['status']}</span></div>"; ?></div>
</div>
<?php endif; ?>
</body></html>