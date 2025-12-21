<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$announcements = $conn->query("
    SELECT a.title, a.content, a.posted_at, u.username
    FROM announcements a
    JOIN users u ON a.posted_by = u.user_id
    ORDER BY a.posted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(18px);
    border-radius:26px;
    box-shadow:0 30px 60px rgba(30,58,138,0.25);
    border:1px solid rgba(255,255,255,0.45);
    padding:40px;
    width:100%;
    max-width:900px;
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
    animation: textGlow 10s ease infinite;
}

@keyframes textGlow{
    0%{background-position:0% center;}
    50%{background-position:100% center;}
    100%{background-position:0% center;}
}

.card{
    border-radius:18px;
    backdrop-filter: blur(12px);
    background: rgba(255,255,255,0.85);
    border:1px solid rgba(255,255,255,0.4);
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

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-bell-fill text-primary fs-3 me-2"></i>
        <h2 class="animated-gradient-text mb-0">Notifications</h2>
    </div>

    <?php if($announcements->num_rows > 0){ ?>
        <?php while($row = $announcements->fetch_assoc()){ ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small class="text-muted">
                        Posted by <?= htmlspecialchars($row['username']) ?> |
                        <?= date('M d, Y h:i A', strtotime($row['posted_at'])) ?>
                    </small>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="alert alert-info text-center">
            No notifications available.
        </div>
    <?php } ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">
        Back to Dashboard
    </a>

</div>

</body>
</html>