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

$classes = $conn->query("
    SELECT 
        sub.subject,
        sub.units,
        sub.subject_id,
        f.faculty_name
    FROM enrollment_subjects es
    JOIN enrollment_requests er ON es.request_id = er.request_id
    JOIN subjects sub ON es.subject_id = sub.subject_id
    LEFT JOIN faculties f ON sub.faculty_id = f.faculty_id
    WHERE er.student_id = $student_id
    ORDER BY sub.subject
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EduCore | My Classes</title>
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
    background:rgba(255,255,255,.8);
    backdrop-filter:blur(18px);
    border-radius:22px;
    box-shadow:0 25px 50px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.4);
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

.btn-info{
    background:#2563eb;
    border:none;
}

.btn-info:hover{
    background:#1e40af;
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

<div class="container py-5">

    <h3 class="mb-4 text-center">
        <span>My Enrolled Subjects</span>
    </h3>

    <div class="glass-card p-4">

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Faculty Assigned</th>
                    <th>Assignments</th>
                    <th>Lectures</th>
                </tr>
            </thead>
            <tbody>
            <?php if($classes->num_rows > 0){ ?>
                <?php while($row = $classes->fetch_assoc()){ ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td><?= htmlspecialchars($row['units']) ?></td>
                        <td>
                            <?= $row['faculty_name']
                                ? htmlspecialchars($row['faculty_name'])
                                : '<span class="text-muted">Not Assigned</span>' ?>
                        </td>
                        <td>
                            <a href="subject_files.php?subject_id=<?= $row['subject_id'] ?>&type=assignment"
                               class="btn btn-sm btn-success">
                                View Assignments
                            </a>
                        </td>
                        <td>
                            <a href="subject_files.php?subject_id=<?= $row['subject_id'] ?>&type=lecture"
                               class="btn btn-sm btn-info">
                                View Lectures
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" class="text-center">No enrolled subjects found.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <div class="text-end mt-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

    </div>

</div>

</body>
</html>