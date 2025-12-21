<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Admin'){
    header("Location: ../index.php");
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid request.");
}

$faculty_id = (int)$_GET['id'];

$faculty = $conn->query("
    SELECT 
        f.faculty_id,
        f.faculty_name,
        f.department,
        u.username,
        u.email,
        r.role_name,
        c.college_name
    FROM faculties f
    JOIN users u ON f.user_id = u.user_id
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN colleges c ON f.college_id = c.college_id
    WHERE f.faculty_id = $faculty_id
")->fetch_assoc();

if(!$faculty){
    die("Faculty record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#1e3a8a;
    --accent:#f59e0b;
}

*{ font-family:'Poppins',sans-serif; }

body{
    margin:0;
    min-height:100vh;
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

@keyframes bgMove{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.glass-card{
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(18px);
    border-radius:26px;
    box-shadow:0 30px 60px rgba(30,58,138,0.25);
    border:1px solid rgba(255,255,255,0.45);
    padding:40px;
    width:100%;
    max-width:800px;
}

.animated-gradient-text{
    font-size:2rem;
    font-weight:800;
    text-align:center;
    margin-bottom:30px;
    background: linear-gradient(90deg, #1e3a8a, #2563eb, #f59e0b);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: textGlow 20s ease infinite;
}

@keyframes textGlow{
    0%{background-position:0% center;}
    50%{background-position:100% center;}
    100%{background-position:0% center;}
}

.card{
    border-radius:22px;
    box-shadow:0 15px 40px rgba(30,58,138,.15);
}

.profile-info p{
    margin-bottom:12px;
    font-size:1.05rem;
}
.profile-info strong{
    color:#1e3a8a;
}

.btn-secondary{
    background:#6b7280;
    border:none;
}
.btn-secondary:hover{
    background:#4b5563;
}
</style>
</head>
<body>

<div class="glass-card">
    <h2 class="animated-gradient-text">Faculty Profile</h2>

    <div class="card mb-4">
        <div class="card-body profile-info">
            <p><strong>Faculty ID:</strong> <?= $faculty['faculty_id'] ?></p>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($faculty['faculty_name']) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($faculty['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($faculty['email'] ?? 'Not provided') ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($faculty['role_name']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($faculty['department']) ?></p>
            <p><strong>College:</strong> <?= htmlspecialchars($faculty['college_name'] ?? 'Not assigned') ?></p>
        </div>
    </div>

    <a href="manage_faculties.php" class="btn btn-secondary mt-3">Back to Manage Faculties</a>
</div>

</body>
</html>