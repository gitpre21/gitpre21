<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin'){
    header("Location: ../index.php");
    exit();
}

if(empty($_GET['student_id'])){
    echo "<div class='alert alert-danger'>No student selected. <a href='manage_students.php'>Back</a></div>";
    exit();
}
$student_id = (int)$_GET['student_id'];

$stmt = $conn->prepare("
    SELECT 
        s.student_id, u.user_id,
        u.first_name, s.middle_name, u.last_name, u.email, 
        s.student_number, s.year_level, s.contact, s.address, s.birthday, s.gender,
        s.guardian_name, s.guardian_contact,
        c.course_name, col.college_name
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    LEFT JOIN colleges col ON s.college_id = col.college_id
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "<div class='alert alert-danger'>Student not found. <a href='manage_students.php'>Back</a></div>";
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile</title>
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
    <h2 class="animated-gradient-text">Student Profile</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <?= htmlspecialchars($student['first_name'].' '.$student['middle_name'].' '.$student['last_name']) ?>
            </h5>
            <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
            <p class="card-text"><strong>Student Number:</strong> <?= htmlspecialchars($student['student_number']) ?></p>
            <p class="card-text"><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level'] ?? '-') ?></p>
            <p class="card-text"><strong>Course:</strong> <?= htmlspecialchars($student['course_name'] ?? 'Not assigned') ?></p>
            <p class="card-text"><strong>College:</strong> <?= htmlspecialchars($student['college_name'] ?? 'Not assigned') ?></p>
            <p class="card-text"><strong>Contact:</strong> <?= htmlspecialchars($student['contact'] ?? '-') ?></p>
            <p class="card-text"><strong>Address:</strong> <?= htmlspecialchars($student['address'] ?? '-') ?></p>
            <p class="card-text"><strong>Birthday:</strong> <?= htmlspecialchars($student['birthday'] ?? '-') ?></p>
            <p class="card-text"><strong>Gender:</strong> <?= htmlspecialchars($student['gender'] ?? '-') ?></p>
            <p class="card-text"><strong>Guardian Name:</strong> <?= htmlspecialchars($student['guardian_name'] ?? '-') ?></p>
            <p class="card-text"><strong>Guardian Contact:</strong> <?= htmlspecialchars($student['guardian_contact'] ?? '-') ?></p>
        </div>
    </div>

    <a href="manage_students.php" class="btn btn-secondary mt-3">Back to Manage Students</a>
</div>

</body>
</html>