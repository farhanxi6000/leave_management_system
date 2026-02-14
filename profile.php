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

/* ---------- FETCH USER PROFILE ---------- */
$sql = "SELECT u.full_name, u.email, u.phone_number, u.address, d.department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.department_id
        WHERE u.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Employee Leave Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
                <a href="apply_leave.php" class="nav-link text-white">
                    📝 <span class="sidebar-text">Apply Leave</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="leave_status.php" class="nav-link text-white">
                    📄 <span class="sidebar-text">Leave Status</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="profile.php" class="nav-link text-white fw-bold">
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
        <h2 class="mb-4 text-white">My Profile</h2>

        <div class="card shadow" style="max-width:600px;">
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label text-muted">Full Name</label>
                    <p class="form-control-plaintext fw-semibold">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Email Address</label>
                    <p class="form-control-plaintext">
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Phone Number</label>
                    <p class="form-control-plaintext">
                        <?= $user['phone_number'] ?: 'Not Provided' ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Address</label>
                    <p class="form-control-plaintext">
                        <?= $user['address'] ?: 'Not Provided' ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Department</label>
                    <p class="form-control-plaintext">
                        <?= $user['department_name'] ?? 'Not Assigned' ?>
                    </p>
                </div>

                <a href="employee_dashboard.php" class="btn btn-secondary mt-3">
                    ← Back
                </a>

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
