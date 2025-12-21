<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$price_per_unit = 100;

$student = $conn->query("SELECT student_id FROM students WHERE user_id=$user_id")->fetch_assoc();
if(!$student){
    die("Student record not found.");
}
$student_id = $student['student_id'];

$enrolled = [];
$enrolled_result = $conn->query("
    SELECT es.subject_id 
    FROM enrollment_subjects es
    JOIN enrollment_requests er ON es.request_id = er.request_id
    WHERE er.student_id = $student_id
");

while($e = $enrolled_result->fetch_assoc()){
    $enrolled[] = $e['subject_id'];
}

$subjects = $conn->query("SELECT subject_id, subject, units FROM subjects ORDER BY subject");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EduCore | Enroll Subjects</title>
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
    background:
      radial-gradient(circle at 20% 20%, rgba(37,99,235,.18), transparent 45%),
      radial-gradient(circle at 80% 80%, rgba(245,158,11,.18), transparent 45%),
      linear-gradient(120deg,#f8fafc,#eef2ff);
    background-size:200% 200%;
    animation:bgMove 14s ease infinite;
}

@keyframes bgMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.glass-card{
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(18px);
    border-radius:22px;
    box-shadow:0 30px 60px rgba(30,58,138,.25);
    border:1px solid rgba(255,255,255,.45);
}

h3 span{
    background:linear-gradient(90deg,#1e3a8a,#2563eb,#f59e0b);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.table{
    border-radius:16px;
    overflow:hidden;
}

.table thead{
    background:#0f172a;
    color:#fff;
}

.badge{
    font-size:.85rem;
}

.btn-primary{
    background:#2563eb;
    border:none;
}

.btn-primary:hover{
    background:#1e40af;
}

.summary-box{
    background:rgba(30,58,138,.08);
    border-radius:14px;
    padding:15px;
}
</style>
</head>

<body>

<div class="container py-5">

    <h3 class="mb-2 text-center">
        <span>Enroll Subjects</span>
    </h3>
    <p class="text-center text-muted mb-4">
        Maximum of <strong>24 Units</strong> • ₱<?= $price_per_unit ?> per unit
    </p>

    <div class="glass-card p-4">

        <form method="POST" action="submit_enrollment.php">

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th class="text-center">Select</th>
                    <th>Subject</th>
                    <th>Units</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $subjects->fetch_assoc()):
                $alreadyEnrolled = in_array($row['subject_id'], $enrolled);
                $subjectPrice = $row['units'] * $price_per_unit;
            ?>
            <tr>
                <td class="text-center">
                    <input type="checkbox"
                           name="subjects[]"
                           value="<?= $row['subject_id'] ?>"
                           data-units="<?= $row['units'] ?>"
                           data-price="<?= $subjectPrice ?>"
                           class="form-check-input subject-check"
                           <?= $alreadyEnrolled ? 'disabled' : '' ?>>
                </td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= $row['units'] ?></td>
                <td>₱<?= number_format($subjectPrice,2) ?></td>
                <td>
                    <?php if($alreadyEnrolled): ?>
                        <span class="badge bg-success">Already Enrolled</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Available</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="summary-box mt-3">
            <strong>Total Units:</strong> <span id="totalUnits">0</span> / 24 <br>
            <strong>Total Amount:</strong> ₱<span id="totalAmount">0.00</span>
        </div>

        <input type="hidden" name="student_id" value="<?= $student_id ?>">

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-4">Submit Enrollment</button>
            <a href="dashboard.php" class="btn btn-secondary ms-2">Back</a>
        </div>

        </form>

    </div>

</div>

<script>
let totalUnits = 0;
let totalAmount = 0;

document.querySelectorAll('.subject-check').forEach(cb => {
    cb.addEventListener('change', function(){
        let units = parseInt(this.dataset.units);
        let price = parseFloat(this.dataset.price);

        if(this.checked){
            if(totalUnits + units > 24){
                alert("Maximum of 24 units only!");
                this.checked = false;
                return;
            }
            totalUnits += units;
            totalAmount += price;
        } else {
            totalUnits -= units;
            totalAmount -= price;
        }

        document.getElementById('totalUnits').innerText = totalUnits;
        document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);
    });
});
</script>

</body>
</html>