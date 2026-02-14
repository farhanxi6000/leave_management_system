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

/* ---------- HANDLE APPROVE / REJECT ---------- */
if (isset($_GET['id'], $_GET['action'])) {
    $leave_id = $_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['Approved', 'Rejected'])) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE leave_requests SET status = ? WHERE leave_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "si", $action, $leave_id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: approve_leave.php");
    exit;
}

/* ---------- FETCH LEAVE REQUESTS ---------- */
$sql = "SELECT lr.leave_id,
               u.full_name,
               d.department_name,
               lt.leave_type_name,
               lr.start_date,
               lr.end_date,
               lr.status,
               lr.document
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.user_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
        ORDER BY lr.date_submitted DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Leave | Employee Leave Management</title>
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
    background-image: url("background3.jpeg"); 
    background-size: cover;
    background-position: center;
    filter: blur(8px);
    transform: scale(1.1); 
    z-index: -2;
}

/* Dark overlay for readability */
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
    <div class="sidebar bg-dark text-white p-3 collapsed" id="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="sidebar-text mb-0">Admin Panel</h5>
            <span class="toggle-btn" onclick="toggleSidebar()">☰</span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="admin_dashboard.php" class="nav-link text-white">
                    📊 <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_users.php" class="nav-link text-white">
                    👥 <span class="sidebar-text">Manage Users</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="approve_leave.php" class="nav-link text-white fw-bold">
                    ✅ <span class="sidebar-text">Approve Leave</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="leave_report.php" class="nav-link text-white">
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

    <!-- CONTENT -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Approve Leave</h2>

        <div class="card shadow">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    No leave applications found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= $row['department_name'] ?? 'N/A' ?></td>
                                    <td><?= $row['leave_type_name'] ?></td>
                                    <td><?= $row['start_date'] ?></td>
                                    <td><?= $row['end_date'] ?></td>

                                    <td>
                                        <?php if (!empty($row['document'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['document']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-info">
                                                View
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

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

                                    <td>
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <a href="?id=<?= $row['leave_id'] ?>&action=Approved"
                                               class="btn btn-success btn-sm">
                                                Approve
                                            </a>
                                            <a href="?id=<?= $row['leave_id'] ?>&action=Rejected"
                                               class="btn btn-danger btn-sm">
                                                Reject
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>

                </table>

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
