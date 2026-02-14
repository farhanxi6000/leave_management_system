<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'Admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: employee_dashboard.php");
            }
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Leave Application System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            background-image: url("background2.jpg");
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
        }

        .login-title {
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="card shadow login-card">
    <div class="card-body p-4">

        <h3 class="text-center login-title mb-3">
            Login
        </h3>

        <p class="text-center text-muted mb-4">
            Leave Application Management System
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Login
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none">
                ← Back to Home
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

<!-- Simple JavaScript Validation -->
<script>
    document.getElementById("loginForm").addEventListener("submit", function (e) {
        const email = document.querySelector("input[name='email']").value;
        const password = document.querySelector("input[name='password']").value;

        if (email.trim() === "" || password.trim() === "") {
            e.preventDefault();
            alert("Please fill in all fields.");
        }
    });
</script>

</body>
</html>
