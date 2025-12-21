<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

$colleges = $conn->query("SELECT * FROM colleges");

$conn->query("
    INSERT INTO roles (role_name)
    VALUES ('Faculty')
    ON DUPLICATE KEY UPDATE role_name = role_name
");

$role_row = $conn->query("
    SELECT role_id FROM roles
    WHERE role_name = 'Faculty'
    LIMIT 1
")->fetch_assoc();

$role_id = $role_row['role_id'];

if (isset($_POST['add']) || isset($_POST['update'])) {

    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $faculty_name = mysqli_real_escape_string($conn, $_POST['faculty_name']);
    $department   = mysqli_real_escape_string($conn, $_POST['department']);
    $college_id   = (int)$_POST['college_id'];

    if (isset($_POST['add'])) {

        $raw_password = $_POST['password'];

        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        $check = $conn->query("
            SELECT user_id FROM users WHERE username='$username' LIMIT 1
        ");

        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {

            $conn->query("
                INSERT INTO users (role_id, username, password, first_name)
                VALUES ($role_id, '$username', '$hashed_password', '$faculty_name')
            ");

            $user_id = $conn->insert_id;

            $conn->query("
                INSERT INTO faculties (faculty_name, user_id, department, college_id)
                VALUES ('$faculty_name', $user_id, '$department', $college_id)
            ");

            $success = "Faculty added successfully.";
        }
    }

    if (isset($_POST['update'])) {

        $faculty_id = (int)$_POST['faculty_id'];
        $user_id    = (int)$_POST['user_id'];
        $raw_password = $_POST['password'] ?? '';

        $check_stmt = $conn->prepare("
            SELECT user_id FROM users
            WHERE username = ? AND user_id != ?
            LIMIT 1
        ");
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();

        if ($check_result->num_rows > 0) {
            $error = "Username already exists.";
        } else {

            if (!empty($raw_password)) {

                $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

                $stmt1 = $conn->prepare("
                    UPDATE users
                    SET username = ?, password = ?, first_name = ?
                    WHERE user_id = ?
                ");
                $stmt1->bind_param("sssi", $username, $hashed_password, $faculty_name, $user_id);

            } else {

                $stmt1 = $conn->prepare("
                    UPDATE users
                    SET username = ?, first_name = ?
                    WHERE user_id = ?
                ");
                $stmt1->bind_param("ssi", $username, $faculty_name, $user_id);
            }

            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("
                UPDATE faculties
                SET faculty_name = ?, department = ?, college_id = ?
                WHERE faculty_id = ?
            ");
            $stmt2->bind_param("ssii", $faculty_name, $department, $college_id, $faculty_id);
            $stmt2->execute();
            $stmt2->close();

            $success = "Faculty updated successfully.";
        }
    }
}

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $result = $conn->query("
        SELECT user_id FROM faculties WHERE faculty_id = $id
    ");

    if ($row = $result->fetch_assoc()) {
        $conn->query("DELETE FROM users WHERE user_id = " . $row['user_id']);
    }

    $conn->query("DELETE FROM faculties WHERE faculty_id = $id");

    header("Location: manage_faculties.php");
    exit();
}

$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $conn->query("
        SELECT f.*, u.username
        FROM faculties f
        JOIN users u ON f.user_id = u.user_id
        WHERE f.faculty_id = $id
    ")->fetch_assoc();
}

$faculties = $conn->query("
    SELECT f.*, u.username, r.role_name, c.college_name
    FROM faculties f
    JOIN users u ON f.user_id = u.user_id
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN colleges c ON f.college_id = c.college_id
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Faculties - EduCore</title>
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
    <h2 class="animated-gradient-text">Manage Faculties</h2>

    <?php if(isset($error)){ ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php } ?>
    <?php if(isset($success)){ ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php } ?>

    <form method="POST">
        <?php if($editData){ ?>
            <input type="hidden" name="faculty_id" value="<?= $editData['faculty_id'] ?>">
            <input type="hidden" name="user_id" value="<?= $editData['user_id'] ?>">
        <?php } ?>

        <input type="text" name="username" class="form-control" placeholder="Username" value="<?= $editData['username'] ?? '' ?>" required>
        <?php if(!$editData){ ?>
            <input type="text" name="password" class="form-control" placeholder="Password" required>
        <?php } ?>
        <input type="text" name="faculty_name" class="form-control" placeholder="Full Name" value="<?= $editData['faculty_name'] ?? '' ?>" required>
        <input type="text" name="department" class="form-control" placeholder="Department" value="<?= $editData['department'] ?? '' ?>" required>
        <select name="college_id" class="form-control" required>
            <option value="">Select College</option>
            <?php while($col = $colleges->fetch_assoc()){ ?>
                <option value="<?= $col['college_id'] ?>" <?= isset($editData['college_id']) && $editData['college_id']==$col['college_id'] ? 'selected' : '' ?>>
                    <?= $col['college_name'] ?>
                </option>
            <?php } ?>
        </select>

        <?php if($editData){ ?>
            <button name="update" class="btn btn-primary">Update Faculty</button>
            <a href="manage_faculties.php" class="btn btn-secondary">Cancel</a>
        <?php } else { ?>
            <button name="add" class="btn btn-success">Add Faculty</button>
        <?php } ?>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>College</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $faculties->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['faculty_id'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['faculty_name'] ?></td>
                <td><?= $row['role_name'] ?></td>
                <td><?= $row['department'] ?></td>
                <td><?= $row['college_name'] ?></td>
                <td>
                    <td>
    <a href="?edit=<?= $row['faculty_id'] ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
    <br>
    <a href="view_faculty.php?id=<?= $row['faculty_id'] ?>" class="btn btn-info btn-sm mb-1">View Profile</a>
    <br>
    <a href="?delete=<?= $row['faculty_id'] ?>" 
       class="btn btn-danger btn-sm"
       onclick="return confirm('Delete this faculty?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>