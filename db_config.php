<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'violatap';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');

// Normalizes role string variations (e.g., "COD Admin" -> "cod", "CSO Admin" -> "cso", "Super Admin" -> "superadmin").
if (!function_exists('normalize_role_name')) {
    function normalize_role_name($role): string
    {
        $clean = preg_replace('/[\s_-]+/', '', strtolower(trim((string) $role)));
        if ($clean === 'codadmin') return 'cod';
        if ($clean === 'csoadmin') return 'cso';
        return $clean;
    }
}

// Canonicalizes role string variations to the exact MySQL ENUM string ('Guard', 'COD', 'CSO', 'Superadmin')
if (!function_exists('canonicalize_role')) {
    function canonicalize_role($role): string
    {
        $norm = normalize_role_name($role);
        return match($norm) {
            'cod'        => 'COD',
            'cso'        => 'CSO',
            'superadmin' => 'Superadmin',
            default      => 'Guard'
        };
    }
}

// Centralized dynamic RBAC permission check querying rbac_permissions table.
if (!function_exists('has_module_access')) {
    function has_module_access($role, $moduleKey, $conn): bool
    {
        if (!$conn instanceof mysqli) {
            return false;
        }

        $normRole = normalize_role_name($role);

        // Superadmin retains full system permissions across all modules
        if ($normRole === 'superadmin') {
            return true;
        }

        if (empty(trim((string) $moduleKey))) {
            return false;
        }

        $stmt = $conn->prepare('SELECT is_allowed FROM rbac_permissions WHERE LOWER(REPLACE(REPLACE(role, " ", ""), "_", "")) = ? AND module_key = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ss', $normRole, $moduleKey);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $isAllowed = false;

        if ($result && $row = $result->fetch_assoc()) {
            $isAllowed = ((int) $row['is_allowed'] === 1);
        }

        $stmt->close();
        return $isAllowed;
    }
}

// Session authorization middleware helper.
if (!function_exists('authorize_roles')) {
    function authorize_roles(array $allowed_roles): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role'])) {
            header('Location: index.php?error=unauthorized');
            exit();
        }

        $currentRole = normalize_role_name($_SESSION['role']);
        $allowed = array_map(static fn($role) => normalize_role_name($role), $allowed_roles);

        if (!in_array($currentRole, $allowed, true)) {
            header('Location: index.php?error=unauthorized');
            exit();
        }
    }
}
?>