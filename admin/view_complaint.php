<?php
// NO LOGIN CHECK - DIRECT VIEW DA
$host = "db01.dbhost.dev";
$user = "user_454yr73gy";
$pass = "p454yr73gy";
$db = "db_454yr73gy";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Error: " . $conn->connect_error . " - Database name thappu da"); }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id==0){ die("ID illa da - URL la ?id=4 nu irukka paaru"); }

$res = $conn->query("SELECT * FROM ccms_complaints WHERE id=$id");
if(!$res){ die("Table illa: ".$conn->error); }
$c = $res->fetch_assoc();
if(!$c){ die("Complaint ID $id not found da"); }
?>
<!DOCTYPE html>
<html>
<head><title>Complaint #<?php echo $c['id']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f4f8;">
<nav style="background:#2c3e50;color:white;padding:12px 20px;">CCMS ADMIN <a href="dashboard.php" style="color:white;float:right;text-decoration:none;">← Back to Dashboard</a></nav>
<div class="container mt-4">
<div class="card shadow" style="border:none;">
<div class="card-header bg-primary text-white"><b>#<?php echo $c['id']; ?> - <?php echo htmlspecialchars($c['subject']); ?></b></div>
<div class="card-body">
<p><b>Status:</b> <span class="badge bg-warning text-dark"><?php echo $c['status']; ?></span> | <b>Date:</b> <?php echo $c['created_at']; ?></p>
<hr>
<h6>Description:</h6>
<div class="bg-light p-3 border rounded" style="font-size:16px;"><?php echo nl2br(htmlspecialchars($c['description'])); ?></div>
<hr>
<form method="POST" action="dashboard.php?id=<?php echo $id; ?>" class="row g-2">
<div class="col-md-4"><select name="status" class="form-select"><option>Pending</option><option>In Progress</option><option>Resolved</option></select></div>
<div class="col-md-5"><input name="remark" class="form-control" placeholder="Admin Remark"></div>
<div class="col-md-3"><button class="btn btn-success w-100">Update & Go Back</button></div>
</form>
</div></div></body></html>