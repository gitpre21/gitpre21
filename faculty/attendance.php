<?php
session_start();
include '../db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Faculty'){
    header("Location: ../index.php");
    exit();
}

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : 0;

$students = $conn->query("SELECT s.student_id, u.first_name, u.last_name 
                          FROM students s 
                          JOIN users u ON s.user_id=u.user_id 
                          WHERE s.course_id=$course_id");

if(isset($_POST['mark'])){
    $date = $_POST['date'];
    foreach($_POST['status'] as $student_id => $status){
        $conn->query("INSERT INTO attendance(student_id, subject_id, date, status) 
                      VALUES($student_id, 1, '$date', '$status')"); // subject_id=1 for example
    }
    $success = "Attendance marked!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Mark Attendance</h2>
    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST">
        <input type="date" name="date" class="form-control mb-2" required>
        <table class="table table-bordered">
            <tr><th>Student</th><th>Status</th></tr>
            <?php while($row=$students->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['first_name'].' '.$row['last_name'] ?></td>
                <td>
                    <select name="status[<?= $row['student_id'] ?>]" class="form-control" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </td>
            </tr>
            <?php } ?>
        </table>
        <button name="mark" class="btn btn-success">Submit Attendance</button>
    </form>
    <a href="dashboard.php" class="btn btn-secondary mt-2">Back</a>
</div>
</body>
</html>
