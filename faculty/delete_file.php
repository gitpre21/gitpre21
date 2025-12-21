<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='Faculty'){
    die("Unauthorized");
}

$file_id = (int)$_GET['file_id'];

$file = $conn->query("
    SELECT file_path FROM subject_files WHERE file_id = $file_id
")->fetch_assoc();

if($file){
    unlink("../uploads/subjects/".$file['file_path']);
    $conn->query("DELETE FROM subject_files WHERE file_id = $file_id");
}

header("Location: view_classes.php");
exit();
