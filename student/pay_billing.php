<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$billing_id = isset($_POST['billing_id']) ? (int)$_POST['billing_id'] : 0;

if($billing_id <= 0){
    die("Invalid billing ID.");
}

$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if(!$student){
    die("Student not found.");
}

$student_id = $student['student_id'];

$stmt = $conn->prepare("
    SELECT billing_id 
    FROM billings
    WHERE billing_id = ? AND student_id = ? AND status = 'Unpaid'
");
$stmt->bind_param("ii", $billing_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    $stmt->close();
    die("Invalid or already paid billing.");
}
$stmt->close();

$stmt = $conn->prepare("UPDATE billings SET status = 'Paid' WHERE billing_id = ?");
$stmt->bind_param("i", $billing_id);
$stmt->execute();
$stmt->close();

header("Location: billing.php");
exit();
