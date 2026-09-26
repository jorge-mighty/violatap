<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce strict role authorization: Superadmin and COD Admin only
authorize_roles(['Superadmin', 'COD Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_card'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $nfc_uid    = trim($_POST['nfc_uid'] ?? '');

    if ($student_id <= 0 || empty($nfc_uid)) {
        header("Location: dashboard.php?page=student_nfc_management&error=empty_fields");
        exit();
    }

    // Check if NFC UID is already registered to another student using a prepared statement
    $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE nfc_uid = ? AND student_id != ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("si", $nfc_uid, $student_id);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            header("Location: dashboard.php?page=student_nfc_management&error=card_already_registered");
            exit();
        }
        $checkStmt->close();
    }

    // Update student's NFC UID binding using parameterized prepared statements
    $stmt = $conn->prepare("UPDATE students SET nfc_uid = ? WHERE student_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $nfc_uid, $student_id);
        if ($stmt->execute()) {
            $stmt->close();

            $currentUserId = $_SESSION['user_id'] ?? 0;
            log_activity($conn, $currentUserId, 'Register NFC Card', "Linked NFC UID '{$nfc_uid}' to student ID #{$student_id}.");

            header("Location: dashboard.php?page=student_nfc_management&success=card_registered");
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
