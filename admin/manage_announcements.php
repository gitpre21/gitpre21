<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin'){
    header("Location: ../index.php");
    exit();
}

if(isset($_POST['add'])){
    $stmt = $conn->prepare("
        INSERT INTO announcements (title, content, posted_by)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("ssi", $_POST['title'], $_POST['content'], $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

if(isset($_POST['update'])){
    $stmt = $conn->prepare("
        UPDATE announcements 
        SET title=?, content=?
        WHERE announcement_id=?
    ");
    $stmt->bind_param("ssi", $_POST['title'], $_POST['content'], $_POST['announcement_id']);
    $stmt->execute();
    $stmt->close();
}

if(isset($_GET['delete'])){
    $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
}

$announcements = $conn->query("
    SELECT a.*, u.username 
    FROM announcements a
    JOIN users u ON a.posted_by = u.user_id
    ORDER BY a.posted_at DESC
");

$editData = null;
if(isset($_GET['edit'])){
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcement_id=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Announcements - EduCore</title>
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
    animation: gradientMove 5s ease infinite;
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
    <h2 class="animated-gradient-text">Manage Announcements</h2>

    <form method="POST" class="mb-4">
        <input type="hidden" name="announcement_id" value="<?= $editData['announcement_id'] ?? '' ?>">

        <input type="text" name="title" class="form-control"
               placeholder="Title"
               value="<?= htmlspecialchars($editData['title'] ?? '') ?>"
               required>

        <textarea name="content" class="form-control" required
                  placeholder="Content"><?= htmlspecialchars($editData['content'] ?? '') ?></textarea>

        <?php if($editData){ ?>
            <button name="update" class="btn btn-primary">Update Announcement</button>
            <a href="manage_announcements.php" class="btn btn-secondary">Cancel</a>
        <?php } else { ?>
            <button name="add" class="btn btn-success">Post Announcement</button>
        <?php } ?>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Posted By</th>
            <th>Date</th>
            <th width="180">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $announcements->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['announcement_id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['posted_at'] ?></td>
                <td>
                    <a href="?edit=<?= $row['announcement_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?delete=<?= $row['announcement_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this announcement?')">
                        Delete
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>