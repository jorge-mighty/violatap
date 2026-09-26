<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce session and role authorization for administrative user creation
authorize_roles(['Superadmin', 'COD', 'CSO']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? ''; 
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $school_id_no = trim($_POST['school_id_no'] ?? '');
    
    // Canonicalize role input to exact MySQL ENUM value ('Guard', 'COD', 'CSO', 'Superadmin')
    $rawRole      = trim($_POST['role'] ?? '');
    $role         = canonicalize_role($rawRole);

    if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($role) || empty($school_id_no)) {
        $_SESSION['flash_message'] = "All fields, including ID Number, are required to create a user.";
        $_SESSION['flash_type']    = "error";
        header("Location: dashboard.php?page=user_management");
        exit();
    }

    $currentRole     = trim($_SESSION['role'] ?? '');
    $currentRoleNorm = normalize_role_name($currentRole);
    $targetRoleNorm  = normalize_role_name($role);

    // Prevent creation of Superadmin accounts by non-Superadmin roles
    if ($targetRoleNorm === 'superadmin' && $currentRoleNorm !== 'superadmin') {
        $_SESSION['flash_message'] = "Unauthorized: Only existing Superadmin accounts can create new Superadmin users.";
        $_SESSION['flash_type']    = "error";
        header("Location: dashboard.php?page=user_management");
        exit();
    }

    // CSO accounts are restricted to creating Guard accounts only
    if ($currentRoleNorm === 'cso' && $targetRoleNorm !== 'guard') {
        $_SESSION['flash_message'] = "Unauthorized: CSO accounts are restricted to creating Guard accounts only.";
        $_SESSION['flash_type']    = "error";
        header("Location: dashboard.php?page=user_management");
        exit();
    }

    // Check for duplicate username
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            $_SESSION['flash_message'] = "The auto-generated username '{$username}' already exists. Please adjust the name or ID.";
            $_SESSION['flash_type']    = "error";
            header("Location: dashboard.php?page=user_management");
            exit();
        }
        $checkStmt->close();
    }

    // Secure one-way hashing for password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user with school_id_no
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, school_id_no, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ssssss", $username, $hashedPassword, $first_name, $last_name, $school_id_no, $role);
        if ($stmt->execute()) {
            $stmt->close();

            $currentUserId = $_SESSION['user_id'] ?? 0;
            log_activity($conn, $currentUserId, 'Add User', "Created new {$role} account for username '{$username}'.");

            $_SESSION['temp_toast_title'] = "Account Created Successfully";
            $_SESSION['temp_toast_msg']   = "Temp Password for {$username}: <br><code class='code-box'>" . htmlspecialchars($password) . "</code>";

            header("Location: dashboard.php?page=user_management");
            exit();
        } else {
            $stmt->close();
        }
    }

    $_SESSION['flash_message'] = "A database error occurred while creating the account.";
    $_SESSION['flash_type']    = "error";
    header("Location: dashboard.php?page=user_management");
    exit();
}

header("Location: dashboard.php?page=user_management");
exit();
?>