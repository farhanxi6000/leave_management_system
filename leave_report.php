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

/* ---------- FILTER VALUES ---------- */
$status = $_GET['status'] ?? '';
$department = $_GET['department'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

/* ---------- BUILD QUERY ---------- */
$sql = "SELECT lr.leave_id,
               u.full_name,
               d.department_name,
               lt.leave_type_name,
               lr.start_date,
               lr.end_date,
               lr.status,
               lr.date_submitted,
               lr.document
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.user_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
        WHERE 1=1";

$params = [];
$types = "";

/* Status filter */
if (!empty($status)) {
    $sql .= " AND lr.status = ?";
    $params[] = $status;
    $types .= "s";
}

/* Department filter */
if (!empty($department)) {
    $sql .= " AND d.department_id = ?";
    $params[] = $department;
    $types .= "i";
}

/* Date range filter */
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND lr.start_date >= ? AND lr.end_date <= ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

$sql .= " ORDER BY lr.date_submitted DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* ---------- FETCH DEPARTMENTS ---------- */
$departments = mysqli_query($conn, "SELECT * FROM departments");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Report | Employee Leave Management</title>
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

    <!-- CONTENT -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Leave Report</h2>

        <!-- FILTER FORM -->
        <form method="GET" class="card shadow mb-4">
            <div class="card-body row g-3">

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Approved" <?= $status=='Approved'?'selected':'' ?>>Approved</option>
                        <option value="Rejected" <?= $status=='Rejected'?'selected':'' ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-control">
                        <option value="">All</option>
                        <?php while ($d = mysqli_fetch_assoc($departments)): ?>
                            <option value="<?= $d['department_id'] ?>" <?= $department==$d['department_id']?'selected':'' ?>>
                                <?= $d['department_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">Apply Filters</button>
                    <a href="leave_report.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- REPORT TABLE -->
        <div class="card shadow">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Document</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center">No records found.</td>
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
                                    <td><?= $row['date_submitted'] ?></td>
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
