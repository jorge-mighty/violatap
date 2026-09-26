<?php
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole   = $_SESSION['role'] ?? 'Guard';
$currentUserId = $_SESSION['user_id'] ?? 0;
$currentRoleNorm = normalize_role_name($currentRole);

// Ensure request is POST or contains explicit action parameters
$recordId = (int)($_REQUEST['record_id'] ?? $_REQUEST['verify_id'] ?? $_REQUEST['settle_id'] ?? 0);
$targetAction = trim($_REQUEST['action'] ?? '');

if ($recordId <= 0) {
    header("Location: dashboard.php?page=violation_records&error=" . urlencode("Invalid record reference identifier."));
    exit();
}

// Fetch current record state & validation
$stmt = $conn->prepare("SELECT record_id, status FROM violation_records WHERE record_id = ? LIMIT 1");
if (!$stmt) {
    header("Location: dashboard.php?page=violation_records&error=" . urlencode("Database preparation error."));
    exit();
}

$stmt->bind_param("i", $recordId);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!isset($record)) {
    header("Location: dashboard.php?page=violation_records&error=" . urlencode("Violation record not found."));
    exit();
}

$currentStatus = $record['status'];

// Determine action type based on URL parameters or POST payload
if (isset($_GET['verify_id']) || $targetAction === 'verify') {
    // State Transition: For Verification -> Unsettled (CSO Action)
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'csoadmin' && $currentRoleNorm !== 'cso') {
        header("Location: dashboard.php?page=violation_records&error=" . urlencode("Unauthorized: CSO privileges required to verify violations."));
        exit();
    }

    if ($currentStatus !== 'For Verification') {
        header("Location: dashboard.php?page=violation_records&error=" . urlencode("Invalid state transition: Only records 'For Verification' can be verified."));
        exit();
    }

    $updateStmt = $conn->prepare("UPDATE violation_records SET status = 'Unsettled' WHERE record_id = ? AND status = 'For Verification'");
    if ($updateStmt) {
        $updateStmt->bind_param("i", $recordId);
        $updateStmt->execute();
        if ($updateStmt->affected_rows > 0) {
            log_activity($conn, $currentUserId, 'Violation Verified', "Verified violation record ID #{$recordId}. Status updated to Unsettled.");
        }
        $updateStmt->close();
    }

    header("Location: dashboard.php?page=violation_records&msg=verified");
    exit();

} elseif (isset($_GET['settle_id']) || $targetAction === 'settle') {
    // State Transition: Unsettled -> Settled (COD Action)
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'codadmin' && $currentRoleNorm !== 'cod') {
        header("Location: dashboard.php?page=violation_records&error=" . urlencode("Unauthorized: Committee on Discipline privileges required to settle cases."));
        exit();
    }

    // STRICT STATE VALIDATION: COD is barred from modifying unverified cases
    if ($currentStatus !== 'Unsettled') {
        header("Location: dashboard.php?page=violation_records&error=" . urlencode("Invalid state transition: Only verified 'Unsettled' records can be marked as Settled. Unverified cases cannot be modified."));
        exit();
    }

    $remarks = trim($_POST['remarks'] ?? 'Settled by Office of Student Affairs');

    // Bound strictly to 'Unsettled' 
    $updateStmt = $conn->prepare("UPDATE violation_records SET status = 'Settled', remarks = ?, cleared_at = CURRENT_TIMESTAMP WHERE record_id = ? AND status = 'Unsettled'");
    if ($updateStmt) {
        $updateStmt->bind_param("si", $remarks, $recordId);
        $updateStmt->execute();
        if ($updateStmt->affected_rows > 0) {
            log_activity($conn, $currentUserId, 'Violation Settled', "Settled case for violation record ID #{$recordId}. Cleared at timestamp updated.");
        }
        $updateStmt->close();
    }

    header("Location: dashboard.php?page=violation_records&msg=settled");
    exit();
} else {
    header("Location: dashboard.php?page=violation_records&error=" . urlencode("Unknown status update action requested."));
    exit();
}