<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("
    SELECT s.student_number, s.middle_name, s.contact, s.address, s.birthday, s.gender, s.year_level,
           s.guardian_name, s.guardian_contact, s.enrollment_date,
           u.first_name, u.last_name, u.email, u.username,
           c.course_name
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    WHERE s.user_id = $user_id
")->fetch_assoc();

if(!$student){
    die("Student record not found.");
}

$msg = '';
$msgType = 'info';

if(isset($_GET['updated'])){
    $msg = "Profile updated successfully!";
    $msgType = "success";
}

if(isset($_GET['error'])){
    $msg = "Failed to update profile.";
    $msgType = "danger";
}

if(isset($_POST['update_profile'])){
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $contact    = $_POST['contact'];
    $address    = $_POST['address'];
    $birthday   = $_POST['birthday'];
    $gender     = $_POST['gender'];
    $year_level = $_POST['year_level'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_contact = $_POST['guardian_contact'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE user_id=?");
    $stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $conn->prepare("
        UPDATE students 
        SET middle_name=?, contact=?, address=?, birthday=?, gender=?, year_level=?, guardian_name=?, guardian_contact=?
        WHERE user_id=?
    ");
    $stmt2->bind_param(
        "ssssssssi",
        $middle_name,
        $contact,
        $address,
        $birthday,
        $gender,
        $year_level,
        $guardian_name,
        $guardian_contact,
        $user_id
    );

    if($stmt2->execute()){
        header("Location: student_profile.php?updated=1");
        exit();
    } else {
        header("Location: student_profile.php?error=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EduCore | Student Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary:#1e3a8a;
    --accent:#f59e0b;
}

* { font-family: 'Poppins', sans-serif; }

body {
    margin:0;
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
}

@keyframes bgMove {
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.card-glass {
    background: rgba(255,255,255,.8);
    backdrop-filter: blur(18px);
    border-radius: 22px;
    box-shadow: 0 25px 50px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.4);
}

h2 span {
    background: linear-gradient(90deg,#1e3a8a,#2563eb,#f59e0b);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}
</style>
</head>

<body>

<div class="container py-5">

    <h2 class="mb-4 text-center">
        <span>My Student Profile</span>
    </h2>

    <?php if($msg!=''): ?>
        <div class="alert alert-info text-center"><?= $msg ?></div>
    <?php endif; ?>

    <div class="card card-glass mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <?= htmlspecialchars($student['first_name'].' '.$student['middle_name'].' '.$student['last_name']) ?>
            </h5>
            <p><strong>Username:</strong> <?= htmlspecialchars($student['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
            <p><strong>Student Number:</strong> <?= htmlspecialchars($student['student_number']) ?></p>
            <p><strong>Course:</strong> <?= htmlspecialchars($student['course_name'] ?? 'Not assigned') ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
            <p><strong>Birthday:</strong> <?= htmlspecialchars($student['birthday']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></p>
            <p><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level']) ?></p>
            <p><strong>Guardian Name:</strong> <?= htmlspecialchars($student['guardian_name']) ?></p>
            <p><strong>Guardian Contact:</strong> <?= htmlspecialchars($student['guardian_contact']) ?></p>
        </div>
    </div>

    <div class="card card-glass">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Update Profile</h5>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                    </div>
                    <div class="col">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name']) ?>">
                    </div>
                    <div class="col">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>Contact</label>
                        <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($student['contact']) ?>">
                    </div>
                    <div class="col">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($student['address']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>Birthday</label>
                        <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($student['birthday']) ?>">
                    </div>
                    <div class="col">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?= $student['gender']=='Male'?'selected':'' ?>>Male</option>
                            <option value="Female" <?= $student['gender']=='Female'?'selected':'' ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Year Level</label>
                    <input type="text" name="year_level" class="form-control" value="<?= htmlspecialchars($student['year_level']) ?>">
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($student['guardian_name']) ?>">
                    </div>
                    <div class="col">
                        <label>Guardian Contact</label>
                        <input type="text" name="guardian_contact" class="form-control" value="<?= htmlspecialchars($student['guardian_contact']) ?>">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
            </form>
        </div>
    </div>

</div>

</body>
</html>