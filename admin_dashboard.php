<?php
session_start();
include "db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    die("Access denied");
}

/* ---------- DASHBOARD METRICS ---------- */

// Total employees
$totalEmployees = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='Employee'")
)['total'];

// Total leave applications
$totalLeaves = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests")
)['total'];

// Pending leave requests
$pendingLeaves = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'")
)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Employee Leave Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .bg-image {
    position: fixed;
    inset: 0;
    background-image: url("background3.jpeg"); /* <-- your image */
    background-size: cover;
    background-position: center;
    filter: blur(8px);
    transform: scale(1.1); /* prevent edge blur */
    z-index: -2;
}

/* Dark overlay for readability */
.bg-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: -1;
}

        .sidebar {
            width: 240px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-text {
            display: none;
        }

        .toggle-btn {
            font-size: 1.4rem;
            cursor: pointer;
        }

        .content {
            transition: margin-left 0.3s ease;
        }

        .card h2 {
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="bg-image"></div>
<div class="bg-overlay"></div>    

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="sidebar bg-dark text-white p-3 collapsed" id="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 sidebar-text">Admin Panel</h5>
            <span class="toggle-btn text-white" onclick="toggleSidebar()">☰</span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="admin_dashboard.php" class="nav-link text-white fw-bold">
                    📊 <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_users.php" class="nav-link text-white">
                    👥 <span class="sidebar-text">Manage Users</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="approve_leave.php" class="nav-link text-white">
                    ✅ <span class="sidebar-text">Approve Leave</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="leave_report.php" class="nav-link text-white fw-bold">
                    📑 <span class="sidebar-text">Leave Report</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="logout.php" class="nav-link text-danger">
                    🚪 <span class="sidebar-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 p-4 content" id="mainContent">

        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- SUMMARY CARDS -->
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Employees</h6>
                        <h2><?= $totalEmployees ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Leave Applications</h6>
                        <h2><?= $totalLeaves ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4 border-warning">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Pending Approvals</h6>
                        <h2 class="text-warning"><?= $pendingLeaves ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- ALERT -->
        <?php if ($pendingLeaves > 0): ?>
            <div class="alert alert-warning">
                You have <strong><?= $pendingLeaves ?></strong> leave request(s) pending approval.
                <a href="approve_leave.php" class="alert-link">Review now</a>.
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                No pending leave requests at the moment.
            </div>
        <?php endif; ?>

        <!-- QUICK ACTIONS -->
        <div class="mt-4">
            <h5 class="mb-3">Quick Actions</h5>
            <a href="manage_users.php" class="btn btn-primary me-2">
                Manage Users
            </a>
            <a href="approve_leave.php" class="btn btn-success">
                Approve Leave
            </a>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

<!-- Sidebar Toggle Script -->
<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("collapsed");
    }
</script>

</body>
</html>
