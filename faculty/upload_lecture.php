<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Faculty'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$faculty = $conn->query("SELECT faculty_id FROM faculties WHERE user_id=$user_id")->fetch_assoc();
if(!$faculty) die("Faculty record not found.");
$faculty_id = $faculty['faculty_id'];

if(!isset($_GET['subject_id'])){
    die("Invalid subject.");
}

$subject_id = (int)$_GET['subject_id'];
$subjectCheck = $conn->query("SELECT subject_id, subject FROM subjects WHERE subject_id=$subject_id AND faculty_id=$faculty_id");
if($subjectCheck->num_rows == 0) die("Invalid subject.");
$subject = $subjectCheck->fetch_assoc();

if(isset($_POST['upload'])){
    if(isset($_FILES['file']) && $_FILES['file']['error']==0){
        $fileName = $_FILES['file']['name'];
        $fileTmp  = $_FILES['file']['tmp_name'];
        $uploadDir = '../uploads/lectures/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filePath = $uploadDir . basename($fileName);

        if(move_uploaded_file($fileTmp, $filePath)){
            $stmt = $conn->prepare("
                INSERT INTO uploaded_files (subject_id, file_name, file_path, file_type) 
                VALUES (?, ?, ?, 'lecture')
            ");
            $stmt->bind_param("iss", $subject_id, $fileName, $filePath);
            $stmt->execute();
            $stmt->close();
            $msg = "Lecture uploaded successfully!";
        } else {
            $msg = "Failed to upload file.";
        }
    } else {
        $msg = "No file selected.";
    }
}

if(isset($_GET['delete_file_id'])){
    $file_id = (int)$_GET['delete_file_id'];
    $file = $conn->query("SELECT file_path FROM uploaded_files WHERE file_id=$file_id AND subject_id=$subject_id")->fetch_assoc();
    if($file){
        if(file_exists($file['file_path'])) unlink($file['file_path']);
        $conn->query("DELETE FROM uploaded_files WHERE file_id=$file_id");
        header("Location: upload_lecture.php?subject_id=$subject_id");
        exit();
    }
}

$uploadedLectures = $conn->query("
    SELECT file_id, file_name, uploaded_at
    FROM uploaded_files
    WHERE subject_id=$subject_id AND file_type='lecture'
    ORDER BY uploaded_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Lecture - EduCore</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#1e3a8a;
    --accent:#f59e0b;
}

*{ font-family:'Poppins',sans-serif; }

body{
    margin:0;
    min-height:100vh;
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
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(18px);
    border-radius:26px;
    box-shadow:0 30px 60px rgba(30,58,138,0.25);
    border:1px solid rgba(255,255,255,0.45);
    padding:40px;
    width:100%;
    max-width:900px;
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

.btn-primary{
    background:#2563eb;
    border:none;
}
.btn-primary:hover{
    background:#1e40af;
}

.btn-danger{
    background:#f59e0b;
    border:none;
}
.btn-danger:hover{
    background:#d97706;
}

.btn-secondary{
    background:#6b7280;
    border:none;
}
.btn-secondary:hover{
    background:#4b5563;
}
</style>
</head>
<body>

<div class="glass-card">

    <h2 class="animated-gradient-text">Upload Lecture for <br><span><?= htmlspecialchars($subject['subject']) ?></span></h2>

    <?php if(isset($msg)){ ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label>Select File:</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Upload Lecture</button>
        <a href="view_classes.php" class="btn btn-secondary ms-2">Back</a>
    </form>

    <h4 class="mb-3">Uploaded Lectures</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($uploadedLectures->num_rows > 0){ ?>
                <?php while($file = $uploadedLectures->fetch_assoc()){ ?>
                    <tr>
                        <td><?= htmlspecialchars($file['file_name']) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></td>
                        <td>
                            <a href="?subject_id=<?= $subject_id ?>&delete_file_id=<?= $file['file_id'] ?>" 
                               class="btn btn-danger btn-sm" onclick="return confirm('Delete this lecture?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">No lectures uploaded yet.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>