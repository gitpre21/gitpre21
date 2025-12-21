<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Faculty'){
    header("Location: ../index.php");
    exit();
}

$faculty_user_id = $_SESSION['user_id'];
$msg = '';

if(isset($_POST['update_profile'])){
    $faculty_name = $_POST['faculty_name'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $department = $_POST['department'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE user_id=?");
    $stmt->bind_param("sssi", $first_name, $last_name, $email, $faculty_user_id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare("UPDATE faculties SET faculty_name=?, department=? WHERE user_id=?");
    $stmt2->bind_param("ssi", $faculty_name, $department, $faculty_user_id);

    if($stmt2->execute()){
        $msg = "Profile updated successfully.";
    } else {
        $msg = "Failed to update profile.";
    }
    $stmt2->close();
}

$faculty = $conn->query("
    SELECT f.faculty_id, f.faculty_name, f.department, c.college_name,
           u.username, u.email, u.first_name, u.last_name, u.created_at
    FROM faculties f
    JOIN users u ON f.user_id=u.user_id
    LEFT JOIN colleges c ON f.college_id=c.college_id
    WHERE f.user_id=$faculty_user_id
")->fetch_assoc();

if(!$faculty){
    die("Faculty record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Profile - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(120deg,#f8fafc,#eef2ff);
    font-family: 'Poppins', sans-serif;
    padding: 50px 0;
}

.card-glass {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    max-width: 700px;
    margin: auto;
    box-shadow: 0 25px 50px rgba(30,58,138,.2);
}

h2 span {
    background: linear-gradient(90deg,#1e3a8a,#2563eb,#f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

form .form-control {
    margin-bottom: 10px;
}

.btn-success {
    background: #2563eb;
    border: none;
}
.btn-success:hover {
    background: #1e40af;
}
.btn-secondary {
    background: #6b7280;
    border: none;
}
.btn-secondary:hover {
    background: #4b5563;
}
</style>
</head>
<body>

<div class="card-glass">
    <h2 class="text-center mb-4"><span>My Faculty Profile</span></h2>

    <?php if($msg != ''): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Faculty Name</label>
        <input type="text" name="faculty_name" class="form-control" value="<?= htmlspecialchars($faculty['faculty_name']) ?>" required>

        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($faculty['first_name']) ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($faculty['last_name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($faculty['email']) ?>" required>

        <label>Department</label>
        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($faculty['department']) ?>">

        <button type="submit" name="update_profile" class="btn btn-success mt-3">Update Profile</button>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </form>

    <div class="mt-4">
        <p><strong>Username:</strong> <?= htmlspecialchars($faculty['username']) ?></p>
        <p><strong>College:</strong> <?= htmlspecialchars($faculty['college_name'] ?? 'Not assigned') ?></p>
        <p><strong>Member Since:</strong> <?= date("F j, Y", strtotime($faculty['created_at'])) ?></p>
    </div>
</div>

</body>
</html>
