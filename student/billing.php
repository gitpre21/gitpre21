<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student'){
    header("Location: ../index.php");
    exit();
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

$billings = $conn->query("
    SELECT billing_id, description, amount, status, created_at
    FROM billings
    WHERE student_id = $student_id
    ORDER BY created_at DESC
");

$requests = $conn->query("
    SELECT request_id, document_type, status, requested_at
    FROM document_requests
    WHERE student_id = $student_id
    ORDER BY requested_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EduCore | My Billings & Documents</title>
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
    padding:30px;
}

h3 span, h2 span, h4 span{
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
.btn-warning{
    background:#f59e0b;
    border:none;
}
.btn-warning:hover{
    background:#d97706;
}
.btn-success{
    background:#16a34a;
    border:none;
}
.btn-success:hover{
    background:#15803d;
}

.text-muted{
    color:#6b7280 !important;
}

.action-form{
    display:inline-block;
}
</style>
</head>

<body>

<div class="container py-5">

    <div class="glass-card">

        <h3 class="mb-4 text-center"><span>My Billings</span></h3>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-4">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($billings->num_rows > 0){ ?>
                    <?php while($b = $billings->fetch_assoc()){ ?>
                        <tr>
                            <td><?= htmlspecialchars($b['description']) ?></td>
                            <td>₱<?= number_format($b['amount'],2) ?></td>
                            <td>
                                <span class="badge bg-<?= $b['status']=='Paid'?'success':'danger' ?>">
                                    <?= $b['status'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($b['created_at'])) ?></td>
                            <td>
                                <?php if($b['status']=='Unpaid'){ ?>
                                <form method="POST" action="pay_billing.php" class="action-form"
                                      onsubmit="return confirm('Confirm payment?');">
                                    <input type="hidden" name="billing_id" value="<?= $b['billing_id'] ?>">
                                    <button class="btn btn-sm btn-success">Pay</button>
                                </form>
                                <?php } else { echo "<span class='text-muted'>Paid</span>"; } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5" class="text-center text-muted">No billing records.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <h4 class="mt-5 mb-3"><span>Request Documents</span></h4>
        <form method="POST" action="request_document.php" class="action-form">
            <input type="hidden" name="document_type" value="COR">
            <button class="btn btn-primary">Request COR</button>
        </form>

        <form method="POST" action="request_document.php" class="action-form ms-2">
            <input type="hidden" name="document_type" value="GRADE_CARD">
            <button class="btn btn-warning">Request Grade Card</button>
        </form>

        <h4 class="mt-5 mb-3"><span>My Document Requests</span></h4>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Export</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($requests->num_rows > 0){ ?>
                    <?php while($r = $requests->fetch_assoc()){ ?>
                        <tr>
                            <td><?= $r['document_type']=='COR'?'Certificate of Registration':'Grade Card' ?></td>
                            <td>
                                <span class="badge bg-<?= $r['status']=='RELEASED'?'success':'warning' ?>">
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($r['requested_at'])) ?></td>
                            <td>
                                <?php if($r['status']=='RELEASED'){ ?>
                                    <a href="export_document.php?type=<?= $r['document_type'] ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-success">
                                       Export
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">Unavailable</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="4" class="text-center text-muted">No requests found.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>

    </div>

</div>

</body>
</html>