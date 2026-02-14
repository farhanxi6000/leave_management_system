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

/* ---------- MODE SELECTION ---------- */
$mode = $_GET['mode'] ?? 'add';

/* ---------- HANDLE ADD USER ---------- */
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $department_id = $_POST['department_id'] ?: NULL;
    $phone_number = $_POST['phone_number'] ?? NULL;
    $address = $_POST['address'] ?? NULL;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users 
        (full_name, email, password, role, department_id, phone_number, address)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssiss",
        $full_name,
        $email,
        $password,
        $role,
        $department_id,
        $phone_number,
        $address
    );

    mysqli_stmt_execute($stmt);

    $success = "User created successfully.";
}

/* ---------- FETCH DEPARTMENTS ---------- */
$departments = mysqli_query($conn, "SELECT * FROM departments");

/* ---------- FETCH USERS (FOR VIEW MODE) ---------- */
$users = mysqli_query(
    $conn,
    "SELECT u.full_name, u.email, u.role, u.phone_number, u.address, d.department_name
     FROM users u
     LEFT JOIN departments d ON u.department_id = d.department_id
     ORDER BY u.role, u.full_name"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Employee Leave Management</title>
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
                <a href="manage_users.php" class="nav-link text-white fw-bold">
                    👥 <span class="sidebar-text">Manage Users</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="approve_leave.php" class="nav-link text-white">
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
        <h2 class="mb-4">Manage Users</h2>

        <!-- MODE SELECTOR -->
        <select class="form-select mb-4" onchange="location = this.value;">
            <option value="manage_users.php?mode=add" <?= $mode==='add'?'selected':'' ?>>
                Add User
            </option>
            <option value="manage_users.php?mode=view" <?= $mode==='view'?'selected':'' ?>>
                View Users
            </option>
        </select>

        <!-- ADD USER MODE -->
        <?php if ($mode === 'add'): ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <div class="card shadow" style="max-width:600px;">
                <div class="card-body">
                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="Employee">Employee</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">None</option>
                                <?php
                                mysqli_data_seek($departments, 0);
                                while ($d = mysqli_fetch_assoc($departments)):
                                ?>
                                    <option value="<?= $d['department_id'] ?>">
                                        <?= $d['department_name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button class="btn btn-primary">Create User</button>
                    </form>
                </div>
            </div>

        <?php endif; ?>

        <!-- VIEW USERS MODE -->
        <?php if ($mode === 'view'): ?>

            <div class="card shadow">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($users) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        No users found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($u = mysqli_fetch_assoc($users)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= $u['phone_number'] ?? '-' ?></td>
                                        <td><?= $u['address'] ?? '-' ?></td>
                                        <td><?= $u['role'] ?></td>
                                        <td><?= $u['department_name'] ?? 'N/A' ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
