<?php
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id']) || isset($_SESSION['role'])) {
    header("Location: dashboard.php");
    exit();
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ViolaTap</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="assets/images/violatap.png">
    <link rel="apple-touch-icon" href="assets/images/violatap.png">
    <link rel="manifest" href="manifest.json">
</head>
<body class="auth-body-wrapper" id="authBody">
    <main class="auth-main-container">
        <article class="auth-card">
            <section class="auth-brand-side">
                <figure class="auth-brand-logo">
                    <img src="assets/images/ISATU.png" alt="ISAT U Logo" class="brand-logo-img">
                    <img src="assets/images/violatap.png" alt="ViolaTap Logo" class="brand-logo-img">
                </figure>
                <h2 class="brand-title">ViolaTap</h2>
                <p class="brand-subtitle">Office of Student Affairs Services</p>
            </section>

            <section class="auth-form-side">
                <header class="auth-header">
                    <h2>Login into your account</h2>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="status-unsettled error-banner animate-shake">
                        <img src="assets/icons/outline/alert-triangle.svg" alt="Error" class="alert-icon-red">
                        <span>
                            <?php 
                                if ($error === 'unauthorized') echo "Session expired or unauthorized access request.";
                                elseif ($error === 'empty') echo "Please enter both username and password.";
                                else echo "Invalid username or password.";
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST" class="auth-vertical-form">
                    <div class="form-group-vertical">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="filter-input" placeholder="Enter username" required autofocus autocomplete="off">
                    </div>

                    <div class="form-group-vertical">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="filter-input" placeholder="Enter password" required>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary full-width">
                        <img src="assets/icons/outline/login-2.svg" alt="Log In" class="asset-icon-img">
                        <span>LOG IN</span>
                    </button>
                </form>
            </section>
        </article>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
            .then(() => console.log("Service Worker Registered"));
        }
    </script>
</body>
</html>