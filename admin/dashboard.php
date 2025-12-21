<?php
session_start();
include '../db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Admin'){
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - EduCore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e3a8a;
            --accent: #f59e0b;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            margin: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(37,99,235,0.18), transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(245,158,11,0.18), transparent 45%),
                linear-gradient(120deg, #f8fafc, #eef2ff);
                background-size: 200% 200%;
                animation: ambientMove 14s ease infinite;
        }

        @keyframes ambientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Header */
        header {
            height: 70px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 10px 30px rgba(30,58,138,0.15);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
        }

        .logo span {
            color: var(--primary);
        }

        /* Hamburger Menu */
        .menu-btn {
        cursor: pointer;
        width: 30px;
        }

        .menu-btn div {
            height: 3px;
            background: #0f172a;
            margin: 6px 0;
        }

        /* Side Menu */
        .side-menu {
            position: fixed;
            top: 70px;
            right: -260px;
            width: 260px;
            height: calc(100% - 70px);
            background: linear-gradient(180deg, #1e3a8a, #0f172a);
            padding: 20px;
            transition: right 0.35s ease;
            box-shadow: -10px 0 30px rgba(0,0,0,0.25);
        }

        .side-menu.active {
            right: 0;
        }

        .side-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            margin-bottom: 12px;
            border-radius: 12px;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .side-menu a:hover {
            background: rgba(245,158,11,0.25);
          transform: translateX(6px);
        }  

        main {
            margin-top: 70px;
            height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .content-card {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(18px);
            max-width: 900px;
            width: 100%;
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(30,58,138,0.25);
            padding: 60px;
            text-align: center;
            animation: floatIn 0.6s ease;
        }

        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .content-card h1 {
            font-size: 52px;
            font-weight: 800;
            letter-spacing: 1.2px;
            background: linear-gradient(90deg, #1e3a8a, #2563eb, #f59e0b);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textGlow 4s ease infinite;
            margin-bottom: 14px;
        }

        .content-card p {
            font-weight: 500;
            font-size: 18px;
            color: #1e3a8a;
        }

        @keyframes textGlow {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        }
    </style>

</head>
<body>

<header>
    <div class="logo"><span>Edu</span>Core</div>
    <div class="menu-btn" id="menuBtn">
        <div></div>
        <div></div>
        <div></div>
    </div>
</header>

<aside class="side-menu" id="sideMenu">
    <a href="manage_faculties.php"><i class="bi bi-people"></i> Manage Faculties</a>
    <a href="manage_courses.php"><i class="bi bi-book"></i> Manage Courses</a>
    <a href="manage_students.php"><i class="bi bi-person"></i> Manage Students</a>
    <a href="manage_subjects.php"><i class="bi bi-journal-bookmark"></i> Manage Subjects</a>
    <a href="manage_announcements.php"><i class="bi bi-megaphone"></i> Manage Announcements</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</aside>

<main>
    <div class="content-card">
        <h1>Welcome Admin</h1>
        <p class="fs-5 mt-3">
            Manage faculties, courses, students, subjects, and announcements.
        </p>
    </div>
</main>

<script>
const menuBtn = document.getElementById('menuBtn');
const sideMenu = document.getElementById('sideMenu');

menuBtn.addEventListener('click', () => {
    sideMenu.classList.toggle('active');
});
</script>

</body>
</html>