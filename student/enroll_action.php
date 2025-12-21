<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$student_user_id = $_SESSION['user_id'];
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$student = $conn->query("SELECT student_id FROM students WHERE user_id=$student_user_id")->fetch_assoc();
if(!$student) {
    die("Student not found.");
}
$student_id = $student['student_id'];

$check = $conn->query("SELECT * FROM enrollments WHERE student_id=$student_id AND subject_id=$subject_id");
if($check->num_rows > 0){
    $_SESSION['message'] = "You are already enrolled in this subject.";
    header("Location: enroll_subjects.php");
    exit();
}

$stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
$stmt->bind_param("ii", $student_id, $subject_id);
if($stmt->execute()){
    $_SESSION['message'] = "Enrollment successful!";
} else {
    $_SESSION['message'] = "Enrollment failed: " . $conn->error;
}

$stmt->close();
header("Location: enroll_subjects.php");
exit();
