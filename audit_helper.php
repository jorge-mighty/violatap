<?php
if (!function_exists('log_activity')) {
    function log_activity($conn, $user_id, $action, $details) {
        if (!$conn instanceof mysqli) {
            return;
        }

        $safe_action = htmlspecialchars(trim((string)$action), ENT_QUOTES, 'UTF-8');
        $safe_details = htmlspecialchars(trim((string)$details), ENT_QUOTES, 'UTF-8');
        $ip_address = htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', ENT_QUOTES, 'UTF-8');
        $userIdParam = (!empty($user_id) && is_numeric($user_id)) ? (int)$user_id : null;
        
        $stmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("isss", $userIdParam, $safe_action, $safe_details, $ip_address);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>