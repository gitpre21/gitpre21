<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

$courses  = $conn->query("SELECT * FROM courses ORDER BY course_name");
$colleges = $conn->query("SELECT * FROM colleges ORDER BY college_name");

$edit = false;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = $conn->query("
        SELECT s.*, u.*
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.student_id = $edit_id
    ");
    if ($edit_row = $edit_result->fetch_assoc()) {
        $edit = true;
    }
}

if (isset($_POST['save'])) {

    $username       = mysqli_real_escape_string($conn, $_POST['username']);
    $raw_password   = $_POST['password'];
    $first_name     = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name      = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $student_number = mysqli_real_escape_string($conn, $_POST['student_number']);

    $course_id  = !empty($_POST['course_id'])  ? (int)$_POST['course_id']  : "NULL";
    $college_id = !empty($_POST['college_id']) ? (int)$_POST['college_id'] : "NULL";

    $hashed_password = !empty($raw_password)
        ? password_hash($raw_password, PASSWORD_DEFAULT)
        : null;

    $roleRow = $conn->query("SELECT role_id FROM roles WHERE role_name='Student' LIMIT 1")->fetch_assoc();
    $student_role_id = $roleRow['role_id'];

    if (!empty($_POST['student_id'])) {

        $student_id = (int)$_POST['student_id'];
        $user_id    = (int)$_POST['user_id'];

        $check = $conn->query("
            SELECT user_id FROM users 
            WHERE username='$username' AND user_id != $user_id
            LIMIT 1
        ");

        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {

            if (!empty($raw_password)) {
                $conn->query("
                    UPDATE users SET
                        username='$username',
                        password='$hashed_password',
                        first_name='$first_name',
                        last_name='$last_name',
                        email='$email'
                    WHERE user_id=$user_id
                ");
            } else {
                $conn->query("
                    UPDATE users SET
                        username='$username',
                        first_name='$first_name',
                        last_name='$last_name',
                        email='$email'
                    WHERE user_id=$user_id
                ");
            }

            $conn->query("
                UPDATE students SET
                    course_id=$course_id,
                    college_id=$college_id,
                    student_number='$student_number'
                WHERE student_id=$student_id
            ");

            $success = "Student updated successfully.";
        }

    }
    else {

        $check = $conn->query("
            SELECT user_id FROM users WHERE username='$username' LIMIT 1
        ");

        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {

            $conn->query("
                INSERT INTO users (role_id, username, password, first_name, last_name, email)
                VALUES (
                    $student_role_id,
                    '$username',
                    '$hashed_password',
                    '$first_name',
                    '$last_name',
                    '$email'
                )
            ");

            $user_id = $conn->insert_id;

            $conn->query("
                INSERT INTO students (
                    user_id, course_id, college_id, student_number,
                    middle_name, contact, address, birthday, gender,
                    year_level, guardian_name, guardian_contact
                ) VALUES (
                    $user_id, $course_id, $college_id, '$student_number',
                    NULL, NULL, NULL, NULL, NULL,
                    NULL, NULL, NULL
                )
            ");

            $success = "Student added successfully.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $res = $conn->query("SELECT user_id FROM students WHERE student_id = $id");
    if ($row = $res->fetch_assoc()) {
        $conn->query("DELETE FROM users WHERE user_id = " . $row['user_id']);
    }

    $conn->query("DELETE FROM students WHERE student_id = $id");
    header("Location: manage_students.php");
    exit();
}

$students = $conn->query("
    SELECT s.student_id, u.user_id, u.username, u.first_name, u.last_name, u.email,
           s.student_number, c.course_name, col.college_name
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    LEFT JOIN colleges col ON s.college_id = col.college_id
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students - EduCore</title>
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

.btn-primary{
    background:#2563eb;
    border:none;
}
.btn-primary:hover{
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

.btn-info{
    background:#0ea5e9;
    border:none;
}
.btn-info:hover{
    background:#0284c7;
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
    <h2 class="animated-gradient-text">Manage Students</h2>

    <?php if($error){ ?><div class="alert alert-danger"><?= $error ?></div><?php } ?>
    <?php if($success){ ?><div class="alert alert-success"><?= $success ?></div><?php } ?>

    <form method="POST" class="mb-3">
        <input type="hidden" name="student_id" value="<?= $edit ? $edit_row['student_id'] : '' ?>">
        <input type="hidden" name="user_id" value="<?= $edit ? $edit_row['user_id'] : '' ?>">

        <input type="text" name="username" placeholder="Username" class="form-control" required value="<?= $edit ? $edit_row['username'] : '' ?>">
        <input type="text" name="password" placeholder="Password" class="form-control" required value="<?= $edit ? $edit_row['password'] : '' ?>">
        <input type="text" name="first_name" placeholder="First Name" class="form-control" required value="<?= $edit ? $edit_row['first_name'] : '' ?>">
        <input type="text" name="last_name" placeholder="Last Name" class="form-control" required value="<?= $edit ? $edit_row['last_name'] : '' ?>">
        <input type="email" name="email" placeholder="Email" class="form-control" value="<?= $edit ? $edit_row['email'] : '' ?>">
        <input type="text" name="student_number" placeholder="Student Number" class="form-control" required value="<?= $edit ? $edit_row['student_number'] : '' ?>">

        <select name="course_id" class="form-control">
            <option value="">Select Course</option>
            <?php $courses->data_seek(0); while($c=$courses->fetch_assoc()){ ?>
                <option value="<?= $c['course_id'] ?>" <?= $edit && $edit_row['course_id']==$c['course_id'] ? 'selected' : '' ?>>
                    <?= $c['course_name'] ?>
                </option>
            <?php } ?>
        </select>

        <select name="college_id" class="form-control">
            <option value="">Select College</option>
            <?php $colleges->data_seek(0); while($col=$colleges->fetch_assoc()){ ?>
                <option value="<?= $col['college_id'] ?>" <?= $edit && $edit_row['college_id']==$col['college_id'] ? 'selected' : '' ?>>
                    <?= $col['college_name'] ?>
                </option>
            <?php } ?>
        </select>

        <button name="save" class="btn btn-success"><?= $edit ? 'Update' : 'Add' ?> Student</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Student Number</th>
                <th>Course</th>
                <th>College</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row=$students->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['student_id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['student_number']) ?></td>
                <td><?= htmlspecialchars($row['course_name'] ?? 'Not assigned') ?></td>
                <td><?= htmlspecialchars($row['college_name'] ?? 'Not assigned') ?></td>
                <td>
                    <a href="?edit=<?= $row['student_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="?delete=<?= $row['student_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this student?')">Delete</a>
                    <a href="student_profile.php?student_id=<?= $row['student_id'] ?>" class="btn btn-info btn-sm">View Profile</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>