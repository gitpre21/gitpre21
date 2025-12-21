<?php 
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Faculty'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$faculty = $conn->query("
    SELECT faculty_id, faculty_name
    FROM faculties 
    WHERE user_id = $user_id
")->fetch_assoc();

if(!$faculty){
    die("Faculty record not found.");
}

$faculty_id = (int)$faculty['faculty_id'];
$faculty_name = $faculty['faculty_name'];

$subjects = $conn->query("
    SELECT 
        s.subject_id,
        s.subject AS subject,
        s.units,
        (SELECT COUNT(*) 
         FROM enrollments e
         WHERE e.subject_id = s.subject_id) AS student_count
    FROM subjects s
    WHERE s.faculty_id = $faculty_id
    ORDER BY s.subject
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Classes - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#1e3a8a;
    --accent:#f59e0b;
}

body{
    min-height:100vh;
    margin:0;
    font-family:'Poppins',sans-serif;
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
    display:flex;
    align-items:center;
    justify-content:center;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    position: relative;
    background: rgba(255,255,255,.82);
    backdrop-filter: blur(18px);
    border-radius:22px;
    box-shadow:0 30px 60px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.45);
    padding:40px;
    max-width:900px;
    width:100%;
}

.animated-gradient-text {
    font-size:2rem;
    font-weight:700;
    background: linear-gradient(270deg,#1e3a8a,#2563eb,#f59e0b,#1e3a8a);
    background-size: 600% 600%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientMove 20s ease infinite;
    margin-bottom:30px;
}

@keyframes gradientMove {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

table.table {
    background: rgba(255,255,255,0.55);
    border-radius:14px;
    overflow:hidden;
    border:1px solid rgba(0,0,0,0.06);
}

table th {
    background: rgba(243,244,246,0.85);
    color: #111827;
    font-weight:600;
}

table td {
    color:#1f2937;
}

table tbody tr:hover {
    background: rgba(0,0,0,0.035);
}

.btn {
    font-weight:600;
    border-radius:12px;
    padding:6px 12px;
    transition:all .3s ease;
}

.btn-primary{
    background:#2563eb;
    border:none;
}

.btn-primary:hover{
    background:#1e40af;
    transform:translateY(-1px);
}

.btn-success{
    background:#10b981;
    border:none;
}

.btn-success:hover{
    background:#047857;
    transform:translateY(-1px);
}

.btn-secondary{
    background:#9ca3af;
    border:none;
}

.btn-secondary:hover{
    background:#6b7280;
    transform:translateY(-1px);
}
</style>
</head>
<body>

<div class="glass-card">

    <h2 class="animated-gradient-text">My Classes - <?= htmlspecialchars($faculty_name) ?></h2>

    <div class="table-responsive">
    <table class="table table-borderless mb-3">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Units</th>
                <th>Enrolled Students</th>
                <th>Upload Files</th>
            </tr>
        </thead>
        <tbody>
        <?php if($subjects->num_rows > 0){ ?>
            <?php while($s = $subjects->fetch_assoc()){ ?>
                <tr>
                    <td><?= htmlspecialchars($s['subject']) ?></td>
                    <td><?= $s['units'] ?></td>
                    <td>
                       <a href="view_students.php?subject_id=<?= htmlspecialchars($s['subject_id']) ?>"
   class="btn btn-sm btn-primary">
   Students
</a>

                    </td>
                    <td>
                        <a href="upload_assignment.php?subject_id=<?= $s['subject_id'] ?>" 
                           class="btn btn-sm btn-success mb-1">
                           Upload Assignment
                        </a>
                        <br>
                        <a href="upload_lecture.php?subject_id=<?= $s['subject_id'] ?>" 
                           class="btn btn-sm btn-primary">
                           Upload Lecture
                        </a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4" class="text-center text-muted">No assigned subjects.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

</div>

</body>
</html>