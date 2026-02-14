<?php
session_start();
include "db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] !== 'Employee') {
    die("Access denied");
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* ---------- HANDLE FORM SUBMISSION ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];

    /* ---------- HANDLE FILE UPLOAD ---------- */
    $filename = NULL;

    if (!empty($_FILES['document']['name'])) {
        $upload_dir = "uploads/";

        // Create folder if not exists (safety)
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = time() . "_" . basename($_FILES['document']['name']);
        $target_path = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['document']['tmp_name'], $target_path)) {
            $error = "Failed to upload document.";
        }
    }

    /* ---------- INSERT INTO DATABASE ---------- */
    if (!$error) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO leave_requests 
            (user_id, leave_type_id, date_submitted, start_date, end_date, reason, document)
            VALUES (?, ?, CURDATE(), ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iissss",
            $user_id,
            $leave_type,
            $start_date,
            $end_date,
            $reason,
            $filename
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Leave application submitted successfully.";
        } else {
            $error = "Failed to submit leave application.";
        }
    }
}

/* ---------- FETCH LEAVE TYPES ---------- */
$leaveTypes = mysqli_query($conn, "SELECT * FROM leave_types");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Leave | Employee Leave Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f4f6f9; overflow-x:hidden; }
        .sidebar { width:240px; min-height:100vh; transition:.3s; }
        .sidebar.collapsed { width:70px; }
        .sidebar.collapsed .sidebar-text { display:none; }
        .toggle-btn { cursor:pointer; font-size:1.4rem; }

        .bg-image {
            position: fixed;
            inset: 0;
            background-image: url("background4.jpeg"); 
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            transform: scale(1.1); 
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="sidebar bg-primary text-white p-3 collapsed" id="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="sidebar-text mb-0">Employee Panel</h5>
            <span class="toggle-btn text-white" onclick="toggleSidebar()">☰</span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="employee_dashboard.php" class="nav-link text-white">
                    📊 <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="apply_leave.php" class="nav-link text-white fw-bold">
                    📝 <span class="sidebar-text">Apply Leave</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="leave_status.php" class="nav-link text-white">
                    📄 <span class="sidebar-text">Leave Status</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="view_profile.php" class="nav-link text-white">
                    👤 <span class="sidebar-text">My Profile</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="logout.php" class="nav-link text-warning">
                    🚪 <span class="sidebar-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Apply Leave</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card shadow" style="max-width:600px;">
            <div class="card-body">

                <!-- IMPORTANT: enctype MUST be present -->
                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-control" required>
                            <?php while ($lt = mysqli_fetch_assoc($leaveTypes)): ?>
                                <option value="<?= $lt['leave_type_id'] ?>">
                                    <?= $lt['leave_type_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supporting Document (optional)</label>
                        <input type="file" name="document" class="form-control">
                    </div>

                    <button class="btn btn-primary">Submit Application</button>
                    <a href="employee_dashboard.php" class="btn btn-secondary ms-2">
                        ← Back
                    </a>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>

</body>
</html>
