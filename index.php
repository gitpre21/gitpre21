<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    /* PREPARED STATEMENT */
    $stmt = $conn->prepare("
        SELECT u.user_id, u.password, r.role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $row = $result->fetch_assoc();

        /* 🔐 VERIFY HASHED PASSWORD */
        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role']    = $row['role_name'];

            if ($row['role_name'] === 'Admin') {
                header("Location: admin/dashboard.php");
                exit();
            } elseif ($row['role_name'] === 'Faculty') {
                header("Location: faculty/dashboard.php");
                exit();
            } elseif ($row['role_name'] === 'Student') {
                header("Location: student/dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "User not found!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root{
    --primary:#1e3a8a;
    --accent:#f59e0b;
}

*{ font-family:'Poppins',sans-serif; }

body{
    min-height:100vh;
    margin:0;
    display:flex;
    align-items:center;
    justify-content:center;
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    background: rgba(255,255,255,.82);
    backdrop-filter: blur(18px);
    border-radius:22px;
    box-shadow:0 30px 60px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.45);
    padding:40px 30px;
    width:100%;
    max-width:420px;
    text-align:center;
}

.glass-card h3{
    font-size:2rem;
    font-weight:700;
    background: linear-gradient(90deg,#1e3a8a,#2563eb,#f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom:30px;
}

.btn-primary{
    background:#2563eb;
    border:none;
    transition:all .3s ease;
}
.btn-primary:hover{
    background:#1e40af;
    transform:translateY(-2px);
}

.alert{
    font-size:.9rem;
}
</style>
</head>

<body>

<div class="glass-card">
    <h3>EduCore Login</h3>

    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3 text-start">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button name="login" class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>