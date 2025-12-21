<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("
    SELECT student_id 
    FROM students 
    WHERE user_id = $user_id
")->fetch_assoc();

if(!$student){
    die("Student record not found.");
}

$student_id = $student['student_id'];

if(!isset($_GET['subject_id'], $_GET['type'])){
    die("Invalid request.");
}

$subject_id = (int)$_GET['subject_id'];
$type = $_GET['type']; // assignment or lecture
if(!in_array($type, ['assignment','lecture'])){
    die("Invalid file type.");
}

$enrolled = $conn->query("
    SELECT es.* 
    FROM enrollment_subjects es
    JOIN enrollment_requests er ON es.request_id = er.request_id
    WHERE er.student_id = $student_id AND es.subject_id = $subject_id
");
if($enrolled->num_rows == 0){
    die("You are not enrolled in this subject.");
}

$subject = $conn->query("
    SELECT subject FROM subjects WHERE subject_id = $subject_id
")->fetch_assoc();

$files = $conn->query("
    SELECT file_id, file_name, uploaded_at, file_type, deadline 
    FROM uploaded_files 
    WHERE subject_id = $subject_id AND file_type = '$type'
    ORDER BY uploaded_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= ucfirst($type) ?> Files - <?= htmlspecialchars($subject['subject']) ?></title>
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

h1{
    background:linear-gradient(90deg,#1e3a8a,#2563eb,#f59e0b);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.table{
    border-radius:16px;
    overflow:hidden;
}

.table thead{
    background:#0f172a;
    color:#fff;
}

.btn-secondary{
    background:#6b7280;
    border:none;
}
.btn-secondary:hover{
    background:#4b5563;
}

.btn-success{
    background:#16a34a;
    border:none;
}
.btn-success:hover{
    background:#15803d;
}
</style>
</head>
<body>

<div class="glass-card">

    <h1 class="mb-4 text-center">
        <?= ucfirst($type) ?> Files for: <?= htmlspecialchars($subject['subject']) ?>
    </h1>
    
    <div class="table-responsive">
        <table class="table table-bordered mt-3 align-middle">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Deadline</th>
                    <th>Uploaded At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($files->num_rows > 0){ ?>
                <?php while($file = $files->fetch_assoc()){ ?>
                <tr>
                    <td><?= htmlspecialchars($file['file_name']) ?></td>
                    <td>
                        <?= $file['deadline'] 
                            ? date('M d, Y', strtotime($file['deadline'])) 
                            : '<span class="text-muted">No deadline</span>' ?>
                    </td>
                    <td><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></td>
                    <td>
                        <a href="download_file.php?file_id=<?= $file['file_id'] ?>" 
                           class="btn btn-sm btn-success">
                           Download
                        </a>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">No <?= $type ?> files uploaded yet.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <a href="view_class.php" class="btn btn-secondary mt-3">
        Back to Classes
    </a>

</div>

</body>
</html>