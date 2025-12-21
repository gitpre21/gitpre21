<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("SELECT student_id FROM students WHERE user_id=$user_id")->fetch_assoc();
if(!$student){
    die("Student record not found.");
}
$student_id = $student['student_id'];

$subjects = $_POST['subjects'] ?? [];
if(empty($subjects)){
    die("<script>alert('No subjects selected.'); window.history.back();</script>");
}

$total_units = 0;
foreach($subjects as $sid){
    $sid = (int)$sid;
    $res = $conn->query("SELECT units FROM subjects WHERE subject_id=$sid");
    if($res->num_rows == 0) die("Invalid subject selected.");
    $total_units += (int)$res->fetch_assoc()['units'];
}

if($total_units > 24){
    die("<script>alert('Maximum of 24 units only.'); window.history.back();</script>");
}

$conn->query("INSERT INTO enrollment_requests (student_id, total_units) VALUES ($student_id, $total_units)") or die($conn->error);
$request_id = $conn->insert_id;

foreach($subjects as $sid){
    $unitRow = $conn->query("SELECT units FROM subjects WHERE subject_id=". (int)$sid)->fetch_assoc();
    $units = (int)$unitRow['units'];
    $conn->query("INSERT INTO enrollment_subjects (request_id, subject_id, units) VALUES ($request_id, $sid, $units)") or die($conn->error);
}

$price_per_unit = 100;
$total_amount = $total_units * $price_per_unit;
$conn->query("INSERT INTO billings (student_id, description, amount) VALUES ($student_id, 'Tuition Fee', $total_amount)") or die($conn->error);

echo "<script>alert('Enrollment successful! Billing generated.'); window.location.href='billing.php';</script>";
exit();
