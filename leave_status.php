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

$sql = "SELECT lt.leave_type_name, lr.start_date, lr.end_date, lr.status
        FROM leave_requests lr
        JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
        WHERE lr.user_id = ?
        ORDER BY lr.date_submitted DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Status | Employee Leave Management</title>
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
                <a href="apply_leave.php" class="nav-link text-white">
                    📝 <span class="sidebar-text">Apply Leave</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="leave_status.php" class="nav-link text-white fw-bold">
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
        <h2 class="mb-4">My Leave Status</h2>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <div class="alert alert-info">
                You have not submitted any leave applications yet.
            </div>
        <?php else: ?>
            <table class="table table-bordered table-striped bg-white">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['leave_type_name'] ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
                            <td>
                                <?php
                                    if ($row['status'] === 'Approved') {
                                        echo "<span class='badge bg-success'>Approved</span>";
                                    } elseif ($row['status'] === 'Rejected') {
                                        echo "<span class='badge bg-danger'>Rejected</span>";
                                    } else {
                                        echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="mt-3">
    <a href="employee_dashboard.php" class="btn btn-secondary">
        ← Back
    </a>
</div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>

</body>
</html>