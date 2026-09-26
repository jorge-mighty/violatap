<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, username, first_name, last_name, password, role FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['role']       = $user['role'];

                log_activity($conn, $user['user_id'], 'Successful Login', "User '{$user['username']}' successfully logged in.");

                header("Location: dashboard.php");
                exit();
            }
        }
        $stmt->close();
    }

    log_activity($conn, null, 'Failed Login Attempt', "Attempted login with username: '{$username}'");
    header("Location: index.php?error=failed");
    exit();
}

header("Location: index.php");
exit();
?>