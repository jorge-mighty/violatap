<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce strict role authorization: Superadmin and COD roles
authorize_roles(['Superadmin', 'COD Admin', 'COD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offense'])) {
    $offense_name = trim($_POST['offense_name'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $sanction     = trim($_POST['sanction'] ?? '');

    if (empty($offense_name) || empty($category) || empty($sanction)) {
        header("Location: dashboard.php?page=code_of_discipline_admin&error=empty_fields");
        exit();
    }

    // Insert new offense record using parameterized prepared statements
    $stmt = $conn->prepare("INSERT INTO code_of_discipline (offense_name, category, description, sanction, status, created_at) VALUES (?, ?, ?, ?, 'Active', NOW())");
    if ($stmt) {
        $stmt->bind_param("ssss", $offense_name, $category, $description, $sanction);
        if ($stmt->execute()) {
            $stmt->close();

            $currentUserId = $_SESSION['user_id'] ?? 0;
            log_activity($conn, $currentUserId, 'Add Offense', "Added offense rule: '{$offense_name}' under category {$category}.");

            header("Location: dashboard.php?page=code_of_discipline_admin&success=offense_added");
            exit();
        } else {
            $stmt->close();
        }
    }

    header("Location: dashboard.php?page=code_of_discipline_admin&error=database_error");
    exit();
}

header("Location: dashboard.php?page=code_of_discipline_admin");
exit();
?>
