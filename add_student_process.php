<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce strict role authorization: Superadmin and COD Admin only
authorize_roles(['Superadmin', 'COD Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id_no = trim($_POST['student_id_no'] ?? '');
    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $department    = trim($_POST['department'] ?? '');
    $year_level    = trim($_POST['year_level'] ?? '');
    $gender        = trim($_POST['gender'] ?? '');
    $nfc_uid       = trim($_POST['nfc_uid'] ?? null);
    
    if ($nfc_uid === '') {
        $nfc_uid = null;
    }

    if (empty($student_id_no) || empty($first_name) || empty($last_name) || empty($department)) {
        header("Location: dashboard.php?page=student_nfc_management&error=empty_fields");
        exit();
    }

    // Check for duplicate student ID using a prepared statement
    $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE student_id_no = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("s", $student_id_no);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            header("Location: dashboard.php?page=student_nfc_management&error=duplicate_id");
            exit();
        }
        $checkStmt->close();
    }

    // Insert new student record using parameterized prepared statements
    $stmt = $conn->prepare("INSERT INTO students (student_id_no, first_name, last_name, department, year_level, gender, nfc_uid, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("sssssss", $student_id_no, $first_name, $last_name, $department, $year_level, $gender, $nfc_uid);
        if ($stmt->execute()) {
            $stmt->close();

            $currentUserId = $_SESSION['user_id'] ?? 0;
            log_activity($conn, $currentUserId, 'Add Student', "Added student record for {$first_name} {$last_name} ({$student_id_no}).");

            header("Location: dashboard.php?page=student_nfc_management&success=student_added");
            exit();
        } else {
            $stmt->close();
        }
    }

    header("Location: dashboard.php?page=student_nfc_management&error=database_error");
    exit();
}

header("Location: dashboard.php?page=student_nfc_management");
exit();
?>