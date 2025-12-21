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

$grades = $conn->query("
    SELECT 
        sub.subject, 
        sub.subject_id, 
        g.grade
    FROM enrollment_subjects es
    JOIN enrollment_requests er ON es.request_id = er.request_id
    JOIN subjects sub ON es.subject_id = sub.subject_id
    LEFT JOIN grades g 
        ON g.student_id = $student_id 
        AND g.subject_id = sub.subject_id
    WHERE er.student_id = $student_id
    ORDER BY sub.subject
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EduCore | My Grades</title>
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
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(18px);
    border-radius:22px;
    box-shadow:0 30px 60px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.45);
}

h3 span{
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

.grade-pill{
    padding:6px 14px;
    border-radius:999px;
    font-weight:600;
    font-size:.9rem;
}

.grade-set{
    background:#dcfce7;
    color:#166534;
}

.grade-missing{
    background:#f1f5f9;
    color:#64748b;
}
</style>
</head>

<body>

<div class="container py-5">

    <h3 class="mb-4 text-center">
        <span>My Grades</span>
    </h3>

    <div class="glass-card p-4">

        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
            <?php if($grades->num_rows > 0){ ?>
                <?php while($row = $grades->fetch_assoc()){ ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td class="text-center">
                            <?php if($row['grade'] !== null): ?>
                                <span class="grade-pill grade-set">
                                    <?= htmlspecialchars($row['grade']) ?>
                                </span>
                            <?php else: ?>
                                <span class="grade-pill grade-missing">
                                    Not Set
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        No subjects found.
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <div class="text-end mt-4">
            <a href="dashboard.php" class="btn btn-secondary">
                Back to Dashboard
            </a>
        </div>

    </div>

</div>

</body>
</html>