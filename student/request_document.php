<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("
    SELECT student_id 
    FROM students 
    WHERE user_id = $user_id
")->fetch_assoc();

if(!$student){
    die("Student not found.");
}

$student_id = $student['student_id'];
$document_type = $_POST['document_type'];

$check = $conn->query("
    SELECT request_id 
    FROM document_requests 
    WHERE student_id=$student_id 
    AND document_type='$document_type'
    AND status='PENDING'
");

if($check->num_rows > 0){
    echo "<script>
        alert('You already have a pending request for this document.');
        window.location.href='billing.php';
    </script>";
    exit();
}

$conn->query("
    INSERT INTO document_requests (student_id, document_type)
    VALUES ($student_id, '$document_type')
") or die($conn->error);

echo "<script>
    alert('Document request submitted successfully!');
    window.location.href='billing.php';
</script>";
