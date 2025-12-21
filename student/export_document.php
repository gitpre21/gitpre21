<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    die("Unauthorized access.");
}

if(!isset($_GET['type'])){
    die("Invalid request.");
}

$doc_type = $_GET['type'];
$user_id  = $_SESSION['user_id'];

$student = $conn->query("
    SELECT s.student_id, s.student_number,
           u.first_name, u.last_name,
           c.course_name
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    JOIN courses c ON s.course_id = c.course_id
    WHERE u.user_id = $user_id
")->fetch_assoc();

if(!$student){
    die("Student record not found.");
}

$student_id = $student['student_id'];

$check = $conn->query("
    SELECT status
    FROM document_requests
    WHERE student_id = $student_id
      AND document_type = '$doc_type'
      AND status = 'RELEASED'
    LIMIT 1
");

if($check->num_rows == 0){
    die("Document not yet released.");
}

$subjects = $conn->query("
    SELECT sub.subject, sub.units
    FROM enrollments e
    JOIN subjects sub ON e.subject_id = sub.subject_id
    WHERE e.student_id = $student_id
");

$title = ($doc_type == 'COR')
    ? "Certificate of Registration"
    : "Grade Card";

echo "<script>window.onload = function(){ window.print(); }</script>";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1, h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h1><?= $title ?></h1>
<h3>School Management System</h3>

<p>
<strong>Name:</strong> <?= $student['first_name'].' '.$student['last_name'] ?><br>
<strong>Student No:</strong> <?= $student['student_number'] ?><br>
<strong>Course:</strong> <?= $student['course_name'] ?><br>
<strong>Date:</strong> <?= date('F d, Y') ?>
</p>

<table>
<tr>
    <th>Subject</th>
    <th>Units</th>
</tr>

<?php
$total = 0;
while($row = $subjects->fetch_assoc()){
    $total += $row['units'];
?>
<tr>
    <td><?= htmlspecialchars($row['subject']) ?></td>
    <td><?= $row['units'] ?></td>
</tr>
<?php } ?>

<tr>
    <th>Total Units</th>
    <th><?= $total ?></th>
</tr>
</table>

<br><br>

<p>
Prepared By: ___________________________<br><br>
Registrar Signature: ____________________
</p>

</body>
</html>
