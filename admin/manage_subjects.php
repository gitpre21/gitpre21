<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Admin'){
    header("Location: ../index.php");
    exit();
}

if(isset($_POST['add'])){
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = (int)$_POST['course_id'];
    $units = (int)$_POST['units'];
    $faculty_id = (int)$_POST['faculty_id'];

    $conn->query("INSERT INTO subjects (subject, description, course_id, units, faculty_id)
                  VALUES ('$subject', '$description', $course_id, $units, $faculty_id)");
}

if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE subject_id=$id");
}

$subjects = $conn->query("
    SELECT s.subject_id, s.subject, s.description, s.units,
           c.course_name,
           f.faculty_name
    FROM subjects s
    JOIN courses c ON s.course_id=c.course_id
    LEFT JOIN faculties f ON s.faculty_id=f.faculty_id
");

$courses = $conn->query("SELECT * FROM courses");
$faculties = $conn->query("SELECT * FROM faculties");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
    padding:20px;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    position: relative;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(18px);
    border-radius:22px;
    box-shadow:0 30px 60px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.45);
    padding:40px;
    max-width:1000px;
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
    text-align:center;
    margin-bottom:30px;
}

@keyframes gradientMove {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

@keyframes gradientMove {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

form .form-control{
    margin-bottom:10px;
    border-radius:10px;
}

form .btn{
    border-radius:12px;
    padding:10px 20px;
    font-weight:600;
    transition:all .3s ease;
}

.btn-success{
    background:#2563eb;
    border:none;
}
.btn-success:hover{
    background:#1e40af;
    transform:translateY(-2px);
}

.btn-secondary{
    background:#f59e0b;
    border:none;
}
.btn-secondary:hover{
    background:#d97706;
    transform:translateY(-2px);
}

.btn-danger{
    background:#f43f5e;
    border:none;
}
.btn-danger:hover{
    background:#b91c1c;
    transform:translateY(-2px);
}

.table{
    margin-top:20px;
    border-radius:12px;
    overflow:hidden;
}
.table th, .table td{
    vertical-align:middle;
}
.table th{
    background: rgba(30,58,138,.85);
    color:#fff;
}
.table tbody tr:hover{
    background: rgba(37,99,235,.08);
}
.text-muted{
    color:#6b7280 !important;
}
</style>
</head>
<body>

<div class="glass-card">
    <h2 class="animated-gradient-text">Manage Subjects</h2>

    <form method="POST" class="mb-4">
        <input type="text" name="subject" placeholder="Subject" class="form-control" required>
        <input type="text" name="description" placeholder="Description" class="form-control" required>
        <input type="number" name="units" placeholder="Units" class="form-control" value="3" required>

        <select name="course_id" class="form-control" required>
            <option value="">Select Course</option>
            <?php while($c=$courses->fetch_assoc()){ ?>
                <option value="<?= $c['course_id'] ?>"><?= $c['course_name'] ?></option>
            <?php } ?>
        </select>

        <select name="faculty_id" class="form-control" required>
            <option value="">Assign Faculty</option>
            <?php while($f=$faculties->fetch_assoc()){ ?>
                <option value="<?= $f['faculty_id'] ?>"><?= $f['faculty_name'] ?></option>
            <?php } ?>
        </select>

        <button name="add" class="btn btn-success">Add Subject</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Units</th>
                <th>Course</th>
                <th>Faculty</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row=$subjects->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['subject_id'] ?></td>
                <td><?= $row['subject'] ?></td>
                <td><?= $row['description'] ?></td>
                <td><?= $row['units'] ?></td>
                <td><?= $row['course_name'] ?></td>
                <td><?= $row['faculty_name'] ?? 'Unassigned' ?></td>
                <td>
                    <a href="?delete=<?= $row['subject_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>