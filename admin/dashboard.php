<?php
 require_once __DIR__ . '/../database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// === STATS ===
$total = $pdo->query("SELECT COUNT(*) FROM ccms_complaints")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Pending'")->fetchColumn();
$progress = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='In Progress'")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Resolved'")->fetchColumn();
$users = $pdo->query("SELECT COUNT(*) FROM ccms_users WHERE role='student'")->fetchColumn();

// Category wise
$cat_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM ccms_complaints GROUP BY category ORDER BY count DESC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent complaints
$recent = $pdo->query("SELECT c.*, u.name FROM ccms_complaints c LEFT JOIN ccms_users u ON c.user_id=u.id ORDER BY c.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Priority wise
$high = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE priority='High' AND status!='Resolved'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:20px; margin-bottom:25px; }
        .stat-card { background:white; padding:20px; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); border-left:4px solid #4F46E5; position:relative; overflow:hidden; }
        .stat-card.pending{border-left-color:#F59E0B} .stat-card.progress{border-left-color:#3B82F6}
        .stat-card.resolved{border-left-color:#10B981} .stat-card.users{border-left-color:#8B5CF6}
        .stat-card h3{font-size:2.2rem; margin:5px 0;} .stat-card p{color:#64748B; font-size:0.9rem}
        .stat-icon{position:absolute; right:15px; top:15px; font-size:2rem; opacity:0.1}
        .grid-2 { display:grid; grid-template-columns:2fr 1fr; gap:20px; }
        @media(max-width:900px){ .grid-2{grid-template-columns:1fr} }
        .badge{padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:600}
        .badge-Pending{background:#FEF3C7;color:#D97706} .badge-In-Progress{background:#DBEAFE;color:#2563EB}
        .badge-Resolved{background:#D1FAE5;color:#059669}
        .urgent-box{background:linear-gradient(135deg,#FEE2E2,#FECACA); border:1px solid #FCA5A5; padding:15px; border-radius:12px; margin-bottom:20px;}
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🎓 Admin Panel</h2>
        <div style="display:flex; gap:15px; align-items:center;">
            <span>Welcome, <b><?= htmlspecialchars($_SESSION['user_name']) ?></b> 👨‍💼</span>
            <a href="complaints.php" style="background:#4F46E5; color:white; padding:8px 16px; border-radius:8px; text-decoration:none;">All Complaints</a>
            <a href="../logout.php" style="color:#EF4444; text-decoration:none;">Logout</a>
        </div>
    </div>

    <div class="container" style="max-width:1200px; margin:0 auto; padding:20px;">

        <?php if($high > 0): ?>
        <div class="urgent-box">
            ⚠️ <b>Alert:</b> <?= $high ?> High Priority complaints pending! <a href="complaints.php?status=Pending" style="color:#DC2626; font-weight:600;">Check Now →</a>
        </div>
        <?php endif; ?>

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <p>Total Complaints</p>
                <h3><?= $total ?></h3>
                <p>All time</p>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <p>Pending</p>
                <h3><?= $pending ?></h3>
                <p>Needs Action</p>
            </div>
            <div class="stat-card progress">
                <div class="stat-icon">🔧</div>
                <p>In Progress</p>
                <h3><?= $progress ?></h3>
                <p>Working on it</p>
            </div>
            <div class="stat-card resolved">
                <div class="stat-icon">✅</div>
                <p>Resolved</p>
                <h3><?= $resolved ?></h3>
                <p><?= $total>0? round($resolved/$total*100):0 ?>% Success Rate</p>
            </div>
            <div class="stat-card users">
                <div class="stat-icon">👨‍🎓</div>
                <p>Total Students</p>
                <h3><?= $users ?></h3>
                <p>Registered</p>
            </div>
        </div>

        <div class="grid-2">
            <!-- RECENT COMPLAINTS -->
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3>Recent Complaints 🕒</h3>
                    <a href="complaints.php" style="color:#4F46E5; text-decoration:none; font-size:0.9rem;">View All →</a>
                </div>
                <?php foreach($recent as $c): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #F1F5F9;">
                        <div>
                            <b><?= htmlspecialchars(substr($c['title'],0,40)) ?><?= strlen($c['title'])>40?'...':'' ?></b><br>
                            <small style="color:#64748B;">by <?= htmlspecialchars($c['name']) ?> • <?= $c['category'] ?> • CMP<?= $c['id'] ?></small>
                        </div>
                        <div>
                            <span class="badge badge-<?= str_replace(' ','-',$c['status']) ?>"><?= $c['status'] ?></span><br>
                            <a href="complaint.php?id=<?= $c['id'] ?>" style="font-size:0.8rem; color:#4F46E5; text-decoration:none;">View →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($recent)): ?><p style="text-align:center; color:#94A3B8; padding:20px;">No complaints yet!</p><?php endif; ?>
            </div>

            <!-- CATEGORY WISE -->
            <div class="card">
                <h3>Category Wise 📊</h3>
                <div style="margin-top:15px;">
                    <?php foreach($categories as $cat): 
                        $percent = $total>0? round($cat['count']/$total*100):0;
                    ?>
                        <div style="margin-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:4px;">
                                <span><?= htmlspecialchars($cat['category'] ?? 'Others') ?></span>
                                <span><b><?= $cat['count'] ?></b> (<?= $percent ?>%)</span>
                            </div>
                            <div style="background:#F1F5F9; height:8px; border-radius:4px; overflow:hidden;">
                                <div style="background:#4F46E5; height:100%; width:<?= $percent ?>%; border-radius:4px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($categories)): ?><p style="color:#94A3B8;">No data</p><?php endif; ?>
                </div>

                <div style="margin-top:25px; background:#F8FAFC; padding:15px; border-radius:8px;">
                    <h4>Quick Actions ⚡</h4>
                    <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                        <a href="complaints.php?status=Pending" style="background:white; border:1px solid #E2E8F0; padding:10px; border-radius:8px; text-decoration:none; color:#334155;">⏳ Pending (<?= $pending ?>) →</a>
                        <a href="complaints.php?status=In Progress" style="background:white; border:1px solid #E2E8F0; padding:10px; border-radius:8px; text-decoration:none; color:#334155;">🔧 In Progress (<?= $progress ?>) →</a>
                        <a href="../register.php" style="background:#EEF2FF; border:1px solid #C7D2FE; padding:10px; border-radius:8px; text-decoration:none; color:#4F46E5;">+ Add Student/Admin</a>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center; margin-top:30px; color:#94A3B8; font-size:0.85rem;">
            Admin Dashboard • College Complaint System © <?= date('Y') ?>
        </div>
    </div>
</body>
</html>