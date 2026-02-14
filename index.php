<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Application Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }

        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            background-image: url("background1.jpeg");
            color: white;
            display: flex;
            align-items: center;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
        }

        .btn-login {
            padding: 12px 30px;
            font-size: 1.1rem;
        }

        footer {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h1>Leave Application<br>Management System</h1>
                <p class="mt-3">
                    A centralized platform to manage employee leave applications
                    efficiently, securely, and transparently.
                </p>

                <button class="btn btn-light btn-login mt-4" onclick="goToLogin()">
                    Login to System
                </button>
            </div>

            <div class="col-md-6 text-center">
                <img
                    src="https://cdn-icons-png.flaticon.com/512/942/942748.png"
                    alt="System Illustration"
                    class="img-fluid"
                    style="max-width: 320px;"
                >
            </div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">System Features</h2>
            <p class="text-muted">Designed to simplify leave management</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">📝</div>
                <h5>Easy Leave Application</h5>
                <p class="text-muted">
                    Employees can submit leave applications online with just a few clicks.
                </p>
            </div>

            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">✅</div>
                <h5>Admin Approval</h5>
                <p class="text-muted">
                    Administrators can approve or reject leave requests efficiently.
                </p>
            </div>

            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">📊</div>
                <h5>Centralized Records</h5>
                <p class="text-muted">
                    All leave records are stored securely in a centralized database.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="py-3">
    <div class="container text-center">
        <small class="text-muted">
            &copy; <?php echo date("Y"); ?> Leave Application Management System
        </small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

<!-- JavaScript -->
<script>
    function goToLogin() {
        window.location.href = "login.php";
    }
</script>

</body>
</html>
