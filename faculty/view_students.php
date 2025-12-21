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
$subjectCheck = $conn->query("
    SELECT subject_id, subject 
    FROM subjects 
    WHERE subject_id=$subject_id AND faculty_id=$faculty_id
");
if($subjectCheck->num_rows == 0) die("Invalid subject.");
$subject = $subjectCheck->fetch_assoc();

if(isset($_POST['save_grade'])){
    $student_id = (int)$_POST['student_id'];
    $grade = $_POST['grade'];
    $allowed = ['1.0','1.5','2.0','2.5','3.0','INC','5.0'];

    if(in_array($grade, $allowed)){
        $stmt = $conn->prepare("
            INSERT INTO grades (student_id, subject_id, grade)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE grade = VALUES(grade)
        ");
        $stmt->bind_param("iis", $student_id, $subject_id, $grade);
        $stmt->execute();
        $stmt->close();

        $msg = "Grade updated successfully.";
    }
}

$students = $conn->query("
    SELECT 
        s.student_id,
        s.student_number,
        u.first_name,
        u.last_name,
        u.email,
        c.course_name,
        g.grade
    FROM enrollment_subjects es
    JOIN enrollment_requests er ON es.request_id = er.request_id
    JOIN students s ON er.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    LEFT JOIN grades g 
        ON g.student_id = s.student_id 
        AND g.subject_id = $subject_id
    WHERE es.subject_id = $subject_id
    ORDER BY u.last_name, u.first_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students Enrolled - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #1e3a8a;
    --accent: #f59e0b;
}

*{ font-family:'Poppins',sans-serif; }

body{
    margin:0;
    min-height:100vh;
    background:
        radial-gradient(circle at 20% 20%, rgba(37,99,235,0.18), transparent 45%),
        radial-gradient(circle at 80% 80%, rgba(245,158,11,0.18), transparent 45%),
        linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

@keyframes bgMove {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.glass-card{
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(18px);
    border-radius:26px;
    box-shadow:0 30px 60px rgba(30,58,138,0.25);
    border:1px solid rgba(255,255,255,0.45);
    padding:40px;
    width:100%;
    max-width:1000px;
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

.table{
    border-radius:16px;
    overflow:hidden;
}

.table thead{
    background:#0f172a;
    color:#fff;
}

.btn-success{
    background:#16a34a;
    border:none;
}
.btn-success:hover{
    background:#15803d;
}

.btn-secondary{
    background:#2563eb;
    border:none;
}
.btn-secondary:hover{
    background:#1e40af;
}

.d-flex .form-select{
    width:auto;
}
</style>
</head>
<body>

<div class="glass-card">

    <h2 class="animated-gradient-text">Students Enrolled in <br><span><?= htmlspecialchars($subject['subject']) ?></span></h2>

    <?php if(isset($msg)){ ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php } ?>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
            <?php if($students && $students->num_rows > 0){ ?>
                <?php while($row = $students->fetch_assoc()){ ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_number']) ?></td>
                        <td><?= htmlspecialchars($row['first_name']." ".$row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['course_name'] ?? 'Not Assigned') ?></td>
                        <td class="d-flex align-items-center">
                            <span class="me-2"><?= $row['grade'] ?? '-' ?></span>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                <select name="grade" class="form-select form-select-sm me-2" required>
                                    <?php
                                    $grades = ['1.0','1.5','2.0','2.5','3.0','INC','5.0'];
                                    foreach($grades as $g){
                                        $selected = ($row['grade'] === $g) ? 'selected' : '';
                                        echo "<option value='$g' $selected>$g</option>";
                                    }
                                    ?>
                                </select>
                                <button name="save_grade" class="btn btn-success btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No students enrolled.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-4">
        <a href="view_classes.php" class="btn btn-secondary">Back to Classes</a>
    </div>

</div>

</body>
</html>