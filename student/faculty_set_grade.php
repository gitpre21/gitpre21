<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Faculty'){
    header("Location: ../index.php");
    exit();
}

$faculty_user_id = $_SESSION['user_id'];

$faculty = $conn->query("SELECT faculty_id FROM faculties WHERE user_id=$faculty_user_id")->fetch_assoc();
if(!$faculty) die("Faculty record not found.");
$faculty_id = $faculty['faculty_id'];

if(!isset($_GET['subject_id'])){
    die("Invalid subject.");
}
$subject_id = (int)$_GET['subject_id'];
$subjectCheck = $conn->query("SELECT * FROM subjects WHERE subject_id=$subject_id AND faculty_id=$faculty_id");
if($subjectCheck->num_rows == 0) die("Invalid subject.");
$subject = $subjectCheck->fetch_assoc();

$students = $conn->query("
    SELECT s.student_id, s.student_number, u.first_name, u.last_name, g.grade
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN grades g ON g.student_id = s.student_id AND g.subject_id = e.subject_id
    WHERE e.subject_id = $subject_id
");

if(isset($_POST['set_grades'])){
    foreach($_POST['grades'] as $student_id => $grade){
        $stmt = $conn->prepare("
            INSERT INTO grades (student_id, subject_id, grade) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE grade=VALUES(grade)
        ");
        $stmt->bind_param("iis", $student_id, $subject_id, $grade);
        $stmt->execute();
        $stmt->close();
    }
    $msg = "Grades updated successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set Grades - <?= htmlspecialchars($subject['subject']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Set Grades for: <strong><?= htmlspecialchars($subject['subject']) ?></strong></h3>
    <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <form method="POST">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Student Number</th>
                    <th>Name</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $students->fetch_assoc()){ ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_number']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                    <td>
                        <select name="grades[<?= $row['student_id'] ?>]" class="form-select" required>
                            <option value="">-- Select Grade --</option>
                            <?php foreach(['1.0','1.5','2.0','2.5','3.0','INC','5.0'] as $g): ?>
                                <option value="<?= $g ?>" <?= $row['grade']==$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <button type="submit" name="set_grades" class="btn btn-success">Save Grades</button>
        <a href="view_classes.php" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>
</body>
</html>
