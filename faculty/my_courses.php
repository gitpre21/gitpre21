<?php
session_start();
include '../db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Faculty'){
    header("Location: ../index.php");
    exit();
}

$courses = $conn->query("SELECT c.course_id, c.course_name, f.faculty_name 
                         FROM courses c 
                         JOIN faculties f ON c.faculty_id=f.faculty_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Courses</h2>
    <table class="table table-bordered">
        <tr><th>ID</th><th>Course</th><th>Faculty</th><th>Action</th></tr>
        <?php while($row=$courses->fetch_assoc()){ ?>
        <tr>
            <td><?= $row['course_id'] ?></td>
            <td><?= $row['course_name'] ?></td>
            <td><?= $row['faculty_name'] ?></td>
            <td>
                <a href="attendance.php?course_id=<?= $row['course_id'] ?>" class="btn btn-primary btn-sm">Mark Attendance</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
