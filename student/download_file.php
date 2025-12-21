<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("
    SELECT student_id 
    FROM students 
    WHERE user_id = $user_id
")->fetch_assoc();

if(!$student){
    die("Student record not found.");
}

$student_id = $student['student_id'];

if(!isset($_GET['file_id'])){
    die("Invalid file.");
}

$file_id = (int)$_GET['file_id'];

$file = $conn->query("
    SELECT uf.file_name, uf.file_path, uf.subject_id, s.subject_id 
    FROM uploaded_files uf
    JOIN subjects s ON uf.subject_id = s.subject_id
    JOIN enrollments e ON e.subject_id = s.subject_id
    WHERE uf.file_id = $file_id AND e.student_id = $student_id
")->fetch_assoc();

if(!$file){
    die("File not found or you are not enrolled in this subject.");
}

$filepath = __DIR__ . '/uploads/' . $file['file_path'];

if(!file_exists($filepath)){
    die("File does not exist on server.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit();
