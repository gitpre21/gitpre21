<?php
session_start();
include '../db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Student'){
    header("Location: ../index.php");
    exit();
}

$student_id = $conn->query("SELECT student_id FROM students WHERE user_id=".$_SESSION['user_id'])->fetch_assoc()['student_id'];

$attendance = $conn->query("SELECT a.date, a.status, sub.subject_name 
                            FROM attendance a 
                            JOIN subjects sub ON a.subject_id=sub.subject_id 
                            WHERE a.student_id=$student_id ORDER BY a.date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>My Attendance</h2>
    <table class="table table-bordered">
        <tr><th>Date</th><th>Subject</th><th>Status</th></tr>
        <?php while($row=$attendance->fetch_assoc()){ ?>
        <tr>
            <td><?= $row['date'] ?></td>
            <td><?= $row['subject_name'] ?></td>
            <td><?= $row['status'] ?></td>
        </tr>
        <?php } ?>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
