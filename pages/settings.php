<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserId = $_SESSION['user_id'] ?? 14;
$currentRole = $_SESSION['role'] ?? 'Superadmin';
$currentRoleNorm = normalize_role_name($currentRole);

$backupMessage = $_SESSION['backup_message'] ?? '';
unset($_SESSION['backup_message']);

$message = $_SESSION['settings_message'] ?? '';
unset($_SESSION['settings_message']);

if (!function_exists('safe_redirect')) {
    function safe_redirect($url) {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
            echo "<script>window.location.href = '" . addslashes($url) . "';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url) . "'></noscript>";
            exit;
        }
    }
}

// Enforce Access Boundary for Settings module based on role
$hasSettingsAccess = has_module_access($currentRole, 'settings', $conn);
if (!$hasSettingsAccess && $currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'codadmin' && $currentRoleNorm !== 'cod' && $currentRoleNorm !== 'csoadmin' && $currentRoleNorm !== 'cso') {
    echo "<main class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>You do not have authorization to view or manage system settings.</p></article></main>";
    exit();
}

$profilePic = $_SESSION['profile_pic'] ?? '';
if (empty($profilePic)) {
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    foreach ($allowedExts as $ext) {
        $testPath = 'uploads/profile_pics/user_' . $currentUserId . '.' . $ext;
        if (file_exists($testPath)) {
            $profilePic = $testPath;
            $_SESSION['profile_pic'] = $testPath;
            break;
        }
    }
}

$currentUser = [
    'user_id'      => $currentUserId,
    'username'     => $_SESSION['username'] ?? 'superadmin',
    'first_name'   => $_SESSION['first_name'] ?? '',
    'last_name'    => $_SESSION['last_name'] ?? 'SysAdmin',
    'school_id_no' => $_SESSION['school_id_no'] ?? '',
    'role'         => $_SESSION['role'] ?? 'Superadmin'
];

$userQuery = $conn->prepare("SELECT user_id, username, first_name, last_name, school_id_no, role FROM users WHERE user_id = ?");
if ($userQuery) {
    $userQuery->bind_param("i", $currentUserId);
    if ($userQuery->execute()) {
        $res = $userQuery->get_result();
        if ($dbUser = $res->fetch_assoc()) {
            $currentUser['username']     = $dbUser['username'] ?? $currentUser['username'];
            $currentUser['first_name']   = $dbUser['first_name'] ?? $currentUser['first_name'];
            $currentUser['last_name']    = $dbUser['last_name'] ?? $currentUser['last_name'];
            $currentUser['school_id_no'] = $dbUser['school_id_no'] ?? $currentUser['school_id_no'];
            $currentUser['role']         = $dbUser['role'] ?? $currentUser['role'];
        }
    }
    $userQuery->close();
}

// Action: Update Profile Picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile_pic') {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/profile_pics/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $targetPath = $uploadDir . 'user_' . $currentUserId . '.' . $ext;
            
            foreach ($allowed as $aExt) {
                $oldFile = $uploadDir . 'user_' . $currentUserId . '.' . $aExt;
                if (file_exists($oldFile) && $oldFile !== $targetPath) {
                    @unlink($oldFile);
                }
            }

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $_SESSION['profile_pic'] = $targetPath;
                $_SESSION['settings_message'] = "Profile picture updated successfully.";
                log_activity($conn, $currentUserId, 'Profile Picture Updated', 'Updated account avatar image.');
            } else {
                $_SESSION['settings_message'] = "Error moving uploaded image file.";
            }
        } else {
            $_SESSION['settings_message'] = "Error: Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
        }
    } else {
        $_SESSION['settings_message'] = "Error: Please select a valid image file.";
    }
    safe_redirect("dashboard.php?page=settings&tab=profile");
}

// Action: Delete Profile Picture / Restore Default
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_profile_pic') {
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    $uploadDir = 'uploads/profile_pics/';

    foreach ($allowedExts as $ext) {
        $oldFile = $uploadDir . 'user_' . $currentUserId . '.' . $ext;
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    unset($_SESSION['profile_pic']);
    $_SESSION['settings_message'] = "Profile picture deleted. Restored to letter default.";
    log_activity($conn, $currentUserId, 'Profile Picture Deleted', 'Deleted profile picture and restored to letter default.');
    safe_redirect("dashboard.php?page=settings&tab=profile");
}

// Action: Update Profile Details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $schoolIdNo = trim($_POST['school_id_no'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, school_id_no = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("sssi", $firstName, $lastName, $schoolIdNo, $currentUserId);
        if ($stmt->execute()) {
            $_SESSION['first_name']   = $firstName;
            $_SESSION['last_name']    = $lastName;
            $_SESSION['school_id_no'] = $schoolIdNo;
            $_SESSION['settings_message'] = "Profile details successfully updated.";
            log_activity($conn, $currentUserId, 'Profile Updated', 'Updated profile information for account.');
        } else {
            $_SESSION['settings_message'] = "Error updating profile details.";
        }
        $stmt->close();
    }
    safe_redirect("dashboard.php?page=settings&tab=profile");
}

// Action: Update Account Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $pwdQuery = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    if ($pwdQuery) {
        $pwdQuery->bind_param("i", $currentUserId);
        $pwdQuery->execute();
        $pwdRow = $pwdQuery->get_result()->fetch_assoc();
        $pwdQuery->close();

        if ($pwdRow && password_verify($currentPass, $pwdRow['password'])) {
            if ($newPass === $confirmPass) {
                $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);
                $updatePwd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($updatePwd) {
                    $updatePwd->bind_param("si", $hashedPassword, $currentUserId);
                    $updatePwd->execute();
                    $updatePwd->close();

                    $_SESSION['settings_message'] = "Password changed successfully.";
                    log_activity($conn, $currentUserId, 'Password Changed', 'User password successfully modified.');
                }
            } else {
                $_SESSION['settings_message'] = "Error: New password and confirmation do not match.";
            }
        } else {
            $_SESSION['settings_message'] = "Error: Current password verification failed.";
        }
    }
    safe_redirect("dashboard.php?page=settings&tab=profile");
}

// Backup & Restore Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup_action'])) {
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'codadmin' && $currentRoleNorm !== 'cod') {
        $_SESSION['backup_message'] = "Unauthorized: Administrative privileges required for database backup and restore.";
        safe_redirect("dashboard.php?page=settings&tab=backup");
    }

    if ($_POST['backup_action'] === 'create_backup') {
        $tables = ['users', 'students', 'rbac_permissions', 'code_of_discipline', 'violation_records', 'nfc_cards', 'system_audit_logs'];
        $sqlDump = "-- ViolaTap Database DML Data Backup\n";
        $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $res = $conn->query("SELECT * FROM `$table`");
            if ($res) {
                // Modified to pure DML: Use DELETE instead of DROP/CREATE to preserve schema bounds
                $sqlDump .= "DELETE FROM `$table`;\n";
                
                while ($row = $res->fetch_assoc()) {
                    $fields = array_keys($row);
                    $values = array_values($row);
                    $escapedValues = array_map([$conn, 'real_escape_string'], $values);
                    $sqlDump .= "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES ('" . implode("', '", $escapedValues) . "');\n";
                }
                $sqlDump .= "\n\n";
            }
        }

        $backupFilename = sys_get_temp_dir() . '/violatap_data_backup_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($backupFilename, $sqlDump);

        log_activity($conn, $currentUserId, 'Database Backup Created', 'Database DML data backup (.sql) compiled and downloaded.');

        if (ob_get_level()) ob_clean();

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="violatap_database_backup_' . date('Y-m-d_H-i-s') . '.sql"');
        header('Content-Length: ' . filesize($backupFilename));
        flush();
        readfile($backupFilename);
        unlink($backupFilename);
        exit;
    } elseif ($_POST['backup_action'] === 'restore_backup' && isset($_FILES['backup_file'])) {
        $fileTmp = $_FILES['backup_file']['tmp_name'] ?? '';
        $filename = $_FILES['backup_file']['name'] ?? 'unknown.sql';

        if (!empty($fileTmp) && file_exists($fileTmp)) {
            $sqlContent = file_get_contents($fileTmp);
            
            // STRICT DDL BLOCKER: Reject file if it contains structural commands
            if (preg_match('/\b(DROP|CREATE|ALTER|TRUNCATE|RENAME|GRANT|REVOKE)\b/i', $sqlContent)) {
                $_SESSION['backup_message'] = "Security Block: The uploaded backup file contains forbidden structural DDL commands. Only DML statements (INSERT, DELETE, UPDATE) are permitted.";
                log_activity($conn, $currentUserId, 'Database Restore Blocked', 'Attempted to restore SQL file containing forbidden DDL structural commands.');
                safe_redirect("dashboard.php?page=settings&tab=backup");
            }

            if (!empty($sqlContent)) {
                $conn->query("SET foreign_key_checks = 0");
                if ($conn->multi_query($sqlContent)) {
                    do {
                        if ($result = $conn->store_result()) { $result->free(); }
                    } while ($conn->more_results() && $conn->next_result());
                }
                $conn->query("SET foreign_key_checks = 1");

                $_SESSION['backup_message'] = "Database restored successfully from file: " . htmlspecialchars($filename, ENT_QUOTES);
                log_activity($conn, $currentUserId, 'Database Restored', 'Database state successfully restored from DML file: ' . $filename);
            } else {
                $_SESSION['backup_message'] = "Error: The uploaded SQL file is empty or invalid.";
            }
        }
    }
    safe_redirect("dashboard.php?page=settings&tab=backup");
}

$modules = [
    'mobile_app'             => 'Mobile App Access (NFC Violation Filling)',
    'dashboard'              => 'Analytics & Overview Dashboard',
    'violation_records'      => 'Violation Records & Settlement',
    'code_of_discipline'     => 'Code of Discipline Management',
    'student_nfc_management' => 'Student & NFC Card Operations',
    'user_management'        => 'System User Credentials Management',
    'settings'               => 'Role Access & Permissions (RBAC)',
    'audit_logs'             => 'Audit Logs & System Monitoring'
];

// Editable roles in the RBAC matrix (Super Admin excluded)
$roles = ['COD', 'CSO', 'Guard'];

$defaultPermissions = [
    'COD'  => ['mobile_app', 'dashboard', 'violation_records', 'code_of_discipline', 'student_nfc_management', 'user_management', 'settings', 'audit_logs'],
    'CSO'  => ['mobile_app', 'dashboard', 'violation_records', 'code_of_discipline', 'user_management', 'settings', 'audit_logs'],
    'Guard'      => ['mobile_app']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'codadmin') {
        $_SESSION['settings_message'] = "Unauthorized: Only administrators can modify RBAC policies.";
        safe_redirect("dashboard.php?page=settings&tab=rbac");
    }

    if ($_POST['action'] === 'update_rbac') {
        $conn->query("TRUNCATE TABLE rbac_permissions");
        $submittedPerms = $_POST['perm'] ?? [];
        
        $insertStmt = $conn->prepare("INSERT INTO rbac_permissions (role, module_key, is_allowed) VALUES (?, ?, ?)");
        if ($insertStmt) {
            foreach ($roles as $role) {
                foreach ($modules as $modKey => $modTitle) {
                    $isAllowed = isset($submittedPerms[$role][$modKey]) ? 1 : 0;
                    $insertStmt->bind_param("ssi", $role, $modKey, $isAllowed);
                    $insertStmt->execute();
                }
            }
            // Ensure Super Admin permissions remain full access internally
            foreach ($modules as $modKey => $modTitle) {
                $superRole = 'Super Admin';
                $isAllowed = 1;
                $insertStmt->bind_param("ssi", $superRole, $modKey, $isAllowed);
                $insertStmt->execute();
            }
            $insertStmt->close();
        }
        $_SESSION['settings_message'] = "Role-based access control policies updated successfully.";
        log_activity($conn, $currentUserId, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.');
    } 
    elseif ($_POST['action'] === 'restore_defaults') {
        $conn->query("TRUNCATE TABLE rbac_permissions");
        $insertStmt = $conn->prepare("INSERT INTO rbac_permissions (role, module_key, is_allowed) VALUES (?, ?, 1)");
        if ($insertStmt) {
            foreach ($defaultPermissions as $role => $allowedMods) {
                foreach ($allowedMods as $modKey) {
                    $insertStmt->bind_param("ss", $role, $modKey);
                    $insertStmt->execute();
                }
            }
            // Ensure Super Admin defaults are full access
            foreach ($modules as $modKey => $modTitle) {
                $superRole = 'Super Admin';
                $insertStmt->bind_param("ss", $superRole, $modKey);
                $insertStmt->execute();
            }
            $insertStmt->close();
        }
        $_SESSION['settings_message'] = "RBAC settings successfully restored to default policies.";
        log_activity($conn, $currentUserId, 'RBAC Defaults Restored', 'RBAC settings successfully restored to default policies.');
    }
    safe_redirect("dashboard.php?page=settings&tab=rbac");
}

$dbPermissions = [];
$rolesWithDbData = [];

$res = $conn->query("SELECT role, module_key, is_allowed FROM rbac_permissions");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $r = $row['role'];
        if (normalize_role_name($r) === 'superadmin') continue; // Hide from standard matrix array parsing
        $rolesWithDbData[$r] = true;
        if (!isset($dbPermissions[$r])) {
            $dbPermissions[$r] = [];
        }
        if ((int)$row['is_allowed'] === 1) {
            $dbPermissions[$r][] = $row['module_key'];
        }
    }
}

foreach ($roles as $role) {
    if (!isset($rolesWithDbData[$role])) {
        $dbPermissions[$role] = $defaultPermissions[$role] ?? [];
    }
}

$activeTab = $_GET['tab'] ?? 'profile';
?>

<!-- Floating Confirmation Modal Window -->
<div id="customConfirmModal" class="custom-modal-backdrop hide-element">
    <div class="custom-modal-card">
        <div class="custom-modal-header">
            <strong id="customModalTitle">Confirm Action</strong>
            <button type="button" class="custom-modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p id="customModalMessage">Are you sure you want to proceed with this action?</p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="executeConfirmedAction()">Confirm</button>
        </div>
    </div>
</div>

<!-- Floating Toast Notification Window -->
<div id="toastNotificationContainer" class="toast-floating-wrapper">
    <?php if ($message): ?>
        <?php $isError = (strpos(strtolower($message), 'error') !== false); ?>
        <div class="toast-notification-card <?php echo $isError ? 'toast-error' : 'toast-success'; ?>" id="activeToastCard">
            <div class="toast-icon-wrapper">
                <img src="assets/icons/outline/<?php echo $isError ? 'clock-hour-5.svg' : 'user.svg'; ?>" alt="Status" class="asset-icon-img">
            </div>
            <div class="toast-content-wrapper">
                <strong><?php echo $isError ? 'Action Error' : 'System Notification'; ?></strong>
                <p><?php echo htmlspecialchars($message, ENT_QUOTES); ?></p>
            </div>
            <button type="button" class="toast-close-btn" onclick="dismissToastCard()">&times;</button>
            <div class="toast-progress-bar"></div>
        </div>
    <?php endif; ?>
</div>

<div class="pad-top-16">
    <!-- Chrome Tab Navigation Bar -->
    <nav class="chrome-tabs-container gap-margin-bottom">
        <a href="dashboard.php?page=settings&tab=profile" class="chrome-tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
            <img src="assets/icons/outline/user.svg" alt="Profile" class="asset-icon-img">
            <span>User Account</span>
        </a>
        <?php if ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'codadmin' || $currentRoleNorm === 'cod'): ?>
            <a href="dashboard.php?page=settings&tab=rbac" class="chrome-tab <?php echo $activeTab === 'rbac' ? 'active' : ''; ?>">
                <img src="assets/icons/outline/settings.svg" alt="RBAC" class="asset-icon-img">
                <span>RBAC Permissions</span>
            </a>
            <a href="dashboard.php?page=settings&tab=backup" class="chrome-tab <?php echo $activeTab === 'backup' ? 'active' : ''; ?>">
                <img src="assets/icons/outline/database.svg" alt="Backup" class="asset-icon-img">
                <span>Backup & Restore</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- TAB 1: PROFILE & ACCOUNT MANAGEMENT -->
    <?php if ($activeTab === 'profile'): ?>
        <header class="app-header header-flex">
            <div>
                <h2>Profile and Account Settings</h2>
                <p>Manage personal profile picture, account details, security credentials, and accessibility options.</p>
            </div>
        </header>

        <div class="asymmetric-2col-grid gap-margin-bottom">
            <!-- Left Fixed Column: Profile Picture & System Preferences -->
            <div class="asymmetric-fixed-col">
                <article class="form-card">
                    <header class="app-header">
                        <h3>Profile Picture</h3>
                    </header>

                    <div class="profile-pic-preview-container">
                        <div class="profile-pic-avatar-wrapper">
                            <?php if (!empty($profilePic) && file_exists($profilePic)): ?>
                                <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES); ?>" alt="Profile Picture" class="profile-pic-preview-img">
                            <?php else: ?>
                                <span class="profile-pic-initial"><?php echo strtoupper(substr($currentUser['last_name'] ?? 'S', 0, 1)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="POST" action="dashboard.php?page=settings&tab=profile" enctype="multipart/form-data" class="vertical-form">
                        <input type="hidden" name="action" value="update_profile_pic">

                        <div class="form-group-vertical">
                            <label for="profile_pic">Upload New Image</label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/png, image/jpeg, image/jpg, image/webp" class="filter-input file-input-margin" required>
                        </div>

                        <div class="form-actions-row">
                            <button type="submit" class="btn btn-primary cod-col-equal">
                                <img src="assets/icons/outline/user-square-rounded.svg" alt="Upload Picture" class="asset-icon-img">
                                <span>Change Picture</span>
                            </button>

                            <?php if (!empty($profilePic) && file_exists($profilePic)): ?>
                                <button type="button" class="btn btn-secondary cod-col-equal" onclick="showConfirmModal('Delete Profile Picture', 'Are you sure you want to delete your custom profile picture and restore to letter default?', 'deleteProfilePicForm')">
                                    <img src="assets/icons/outline/restore.svg" alt="Restore Default" class="asset-icon-img">
                                    <span title="Restore Default Picture">Default</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if (!empty($profilePic) && file_exists($profilePic)): ?>
                        <form id="deleteProfilePicForm" method="POST" action="dashboard.php?page=settings&tab=profile" class="hide-element">
                            <input type="hidden" name="action" value="delete_profile_pic">
                        </form>
                    <?php endif; ?>
                </article>

                <article class="form-card">
                    <header class="app-header">
                        <h3>System Preferences & Accessibility</h3>
                    </header>

                    <div class="preference-control-row">
                        <div class="pref-description">
                            <strong>Theme Mode</strong>
                            <p class="text-muted-desc">Toggle between Dark and Light themes.</p>
                        </div>
                        
                        <label class="toggle-switch" title="Toggle Interface Theme">
                            <input type="checkbox" id="prefThemeCheckbox">
                            <span class="toggle-slider">
                                <span class="toggle-knob"></span>
                            </span>
                        </label>
                    </div>

                    <hr class="pref-divider">

                    <div class="accessibility-settings-grid">
                        <div class="form-group-vertical">
                            <label for="tabFontFamilySelect">Font Family</label>
                            <select id="tabFontFamilySelect" class="filter-select">
                                <option value="'Inter', sans-serif">Inter</option>
                                <option value="'Google Sans', sans-serif">Google Sans</option>
                                <option value="'Roboto', sans-serif">Roboto</option>
                                <option value="'Open Sans', sans-serif">Open Sans</option>
                                <option value="system-ui, sans-serif">System UI</option>
                            </select>
                        </div>

                        <div class="form-group-vertical">
                            <label for="tabFontSizeSelect">Font Size Scaling</label>
                            <select id="tabFontSizeSelect" class="filter-select">
                                <option value="0.875">Small (87.5%)</option>
                                <option value="1">Normal (100%)</option>
                                <option value="1.125">Large (112.5%)</option>
                                <option value="1.25">Extra Large (125%)</option>
                            </select>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Right Main Fluid Column: Personal Profile Details & Security -->
            <div class="asymmetric-fluid-col">
                <article class="form-card">
                    <header class="app-header">
                        <h3>Personal Profile Details</h3>
                    </header>
                    <form method="POST" action="dashboard.php?page=settings&tab=profile" class="vertical-form">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group-vertical">
                            <label for="username">Username (Read-Only)</label>
                            <input type="text" id="username" class="filter-input" value="<?php echo htmlspecialchars($currentUser['username'] ?? '', ENT_QUOTES); ?>" disabled>
                        </div>

                        <div class="form-group-vertical">
                            <label for="school_id_no">ID No.</label>
                            <input type="text" id="school_id_no" name="school_id_no" class="filter-input" value="<?php echo htmlspecialchars($currentUser['school_id_no'] ?? '', ENT_QUOTES); ?>" placeholder="e.g. 2020-0000-M">
                        </div>

                        <div class="form-group-vertical">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="filter-input" value="<?php echo htmlspecialchars($currentUser['first_name'] ?? '', ENT_QUOTES); ?>" required>
                        </div>

                        <div class="form-group-vertical">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="filter-input" value="<?php echo htmlspecialchars($currentUser['last_name'] ?? '', ENT_QUOTES); ?>" required>
                        </div>

                        <div class="form-actions-vertical">
                            <button type="submit" class="btn btn-primary full-width">
                                <img src="assets/icons/outline/user.svg" alt="Save Profile" class="asset-icon-img">
                                <span>Update Profile</span>
                            </button>
                        </div>
                    </form>
                </article>

                <article class="form-card">
                    <header class="app-header">
                        <h3>Change Password</h3>
                    </header>
                    <form method="POST" action="dashboard.php?page=settings&tab=profile" class="vertical-form">
                        <input type="hidden" name="action" value="update_password">

                        <div class="form-group-vertical">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="filter-input" placeholder="Enter current password" required>
                        </div>

                        <div class="form-row-2col">
                            <div class="form-group-vertical">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="filter-input" placeholder="Enter new password" required>
                            </div>
                            <div class="form-group-vertical">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="filter-input" placeholder="Confirm new password" required>
                            </div>
                        </div>

                        <div class="form-actions-vertical">
                            <button type="submit" class="btn btn-primary">
                                <img src="assets/icons/outline/check.svg" alt="Update Password" class="asset-icon-img">
                                <span>Update</span>
                            </button>
                        </div>
                    </form>
                </article>
            </div>
        </div>

    <!-- TAB 2: BACKUP & RESTORE MANAGEMENT -->
    <?php elseif ($activeTab === 'backup' && ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'codadmin' || $currentRoleNorm === 'cod')): ?>
        <header class="app-header header-flex gap-margin-bottom">
            <div>
                <h2>Backup and Restore Management</h2>
                <p>Export or import database records and system state configurations as an SQL file.</p>
            </div>
        </header>

        <?php if ($backupMessage): ?>
            <p class="status-settled cod-alert-msg"><?php echo htmlspecialchars($backupMessage, ENT_QUOTES); ?></p>
        <?php endif; ?>

        <div class="form-row-2col gap-margin-bottom">
            <article class="metric-card pad-20 flex-vertical-between">
                <header class="app-header">
                    <h3>Export Database Data</h3>
                    <p class="text-muted-desc">Download an SQL dump file containing all system database records, student records, and settings.</p>
                </header>
                
                <form method="POST" action="dashboard.php?page=settings&tab=backup">
                    <input type="hidden" name="backup_action" value="create_backup">
                    <div class="form-actions-row">
                        <button type="submit" class="btn btn-primary">
                            <img src="assets/icons/outline/database.svg" alt="Download Backup" class="asset-icon-img">
                            <span>Export</span>
                        </button>
                    </div>
                </form>
            </article>

            <article class="metric-card pad-20 flex-vertical-between">
                <header class="app-header">
                    <h3>Restore Database Data</h3>
                    <p class="text-muted-desc">Upload a verified SQL backup file to restore database records and operational configurations.</p>
                </header>
                
                <form id="restoreBackupForm" method="POST" action="dashboard.php?page=settings&tab=backup" enctype="multipart/form-data">
                    <input type="hidden" name="backup_action" value="restore_backup">
                    <div class="form-group-vertical">
                        <input type="file" id="backup_file_input" name="backup_file" accept=".sql" required class="filter-input file-input-margin full-width">
                    </div>
                    <div class="form-actions-row">
                        <button type="button" class="btn btn-secondary" onclick="triggerRestoreBackup()">
                            <img src="assets/icons/outline/restore.svg" alt="Upload & Restore" class="asset-icon-img">
                            <span>Restore</span>
                        </button>
                    </div>
                </form>
            </article>
        </div>

    <!-- TAB 3: ROLE-BASED ACCESS CONTROL (RBAC) -->
    <?php elseif ($activeTab === 'rbac' && ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'codadmin' ||$currentRoleNorm === 'cod')): ?>
        <header class="app-header header-flex-aligned gap-margin-bottom">
            <div>
                <h2>Role-Based Access Control (RBAC) Settings</h2>
                <p>Define administrative access policies, feature visibility, and system permissions.</p>
            </div>
            <div class="header-actions-inline">
                <form id="restoreRbacForm" method="POST" action="dashboard.php?page=settings&tab=rbac">
                    <input type="hidden" name="action" value="restore_defaults">
                    <button type="button" class="btn btn-secondary" onclick="showConfirmModal('Restore Default RBAC', 'Are you sure you want to restore default RBAC permissions?', 'restoreRbacForm')">
                        <img src="assets/icons/outline/restore.svg" alt="Restore Defaults" class="asset-icon-img">
                        <span title="Restore Default RBAC">Defaults</span>
                    </button>
                </form>
            </div>
        </header>

        <div class="rbac-grid gap-margin-bottom">
            <?php foreach ($roles as$role): ?>
                <article class="metric-card rbac-role-card">
                    <div class="role-card-header">
                        <h3><?php echo htmlspecialchars($role, ENT_QUOTES); ?></h3>
                        <span class="status-settled">STANDARD</span>
                    </div>
                    <div class="permission-list">
                        <p class="stat-label">Modules: 
                            <strong><?php echo count($dbPermissions[$role] ?? []); ?> / <?php echo count($modules); ?></strong>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <article class="table-card">
            <header class="app-header header-flex-aligned">
                <h3>System Module Permission Matrix</h3>
            </header>

            <form id="rbacForm" method="POST" action="dashboard.php?page=settings&tab=rbac">
                <input type="hidden" name="action" value="update_rbac">
                <div class="table-responsive">
                    <table class="data-table rbac-matrix-table">
                        <thead class="table-header">
                            <tr>
                                <th>Module / Feature Path</th>
                                <?php foreach ($roles as$role): ?>
                                    <th><?php echo htmlspecialchars($role, ENT_QUOTES); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $modKey =>$modTitle): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($modTitle, ENT_QUOTES); ?></strong><br>
                                        <small class="code-box"><?php echo htmlspecialchars($modKey, ENT_QUOTES); ?></small>
                                    </td>
                                    <?php foreach ($roles as$role): ?>
                                        <?php 
                                            $hasAccess = in_array($modKey, $dbPermissions[$role] ?? []);
                                        ?>
                                        <td>
                                            <div class="toggle-switch-wrapper">
                                                <label class="toggle-switch-local">
                                                    <input type="checkbox" name="perm[<?php echo htmlspecialchars($role, ENT_QUOTES); ?>][<?php echo htmlspecialchars($modKey, ENT_QUOTES); ?>]" value="1" <?php echo $hasAccess ? 'checked' : ''; ?>>
                                                    <img src="assets/icons/outline/toggle-left.svg" alt="Off" class="toggle-icon toggle-icon-off asset-icon-img">
                                                    <img src="assets/icons/outline/toggle-right.svg" alt="On" class="toggle-icon toggle-icon-on asset-icon-img">
                                                </label>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" form="rbacForm" class="btn btn-primary">
                        <img src="assets/icons/outline/device-floppy.svg" alt="Save Access Policies" class="asset-icon-img">
                        <span>Save Policies</span>
                    </button>
                </div>
            </form>
        </article>
    <?php endif; ?>
</div>

<script>
    let pendingFormTarget = null;

    function showConfirmModal(title, message, formId) {
        document.getElementById('customModalTitle').textContent = title;
        document.getElementById('customModalMessage').textContent = message;
        pendingFormTarget = formId;
        const modal = document.getElementById('customConfirmModal');
        if (modal) {
            modal.classList.remove('hide-element');
            modal.classList.add('active');
        }
    }

    function closeConfirmModal() {
        const modal = document.getElementById('customConfirmModal');
        if (modal) {
            modal.classList.add('hide-element');
            modal.classList.remove('active');
        }
        pendingFormTarget = null;
    }

    function executeConfirmedAction() {
        if (pendingFormTarget) {
            const form = document.getElementById(pendingFormTarget);
            if (form) form.submit();
        }
        closeConfirmModal();
    }

    function showToastAlert(message, isError = true) {
        const container = document.getElementById('toastNotificationContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-notification-card ${isError ? 'toast-error' : 'toast-success'}`;
        toast.innerHTML = `
            <div class="toast-icon-wrapper">
                <img src="assets/icons/outline/${isError ? 'clock-hour-5.svg' : 'user.svg'}" alt="Status" class="asset-icon-img">
            </div>
            <div class="toast-content-wrapper">
                <strong>${isError ? 'Action Error' : 'System Notification'}</strong>
                <p>${message}</p>
            </div>
            <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="toast-progress-bar"></div>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('toast-hiding');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function triggerRestoreBackup() {
        const fileInput = document.getElementById('backup_file_input');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            showToastAlert('Please select an SQL backup file before attempting to restore.');
            return;
        }
        showConfirmModal('Restore Database Data', 'WARNING: Restoring will overwrite existing database records. Do you wish to proceed?', 'restoreBackupForm');
    }

    function dismissToastCard() {
        const toast = document.getElementById('activeToastCard');
        if (toast) {
            toast.classList.add('toast-hiding');
            setTimeout(() => { toast.remove(); }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeToast = document.getElementById('activeToastCard');
        if (activeToast) {
            setTimeout(() => { dismissToastCard(); }, 5000);
        }

        const prefThemeCheckbox = document.getElementById('prefThemeCheckbox');
        const menuThemeCheckbox = document.getElementById('menuThemeCheckbox');
        const currentTheme = localStorage.getItem('themePreference') || 'dark';

        if (prefThemeCheckbox) {
            prefThemeCheckbox.checked = (currentTheme === 'light');

            prefThemeCheckbox.addEventListener('change', (e) => {
                const isLight = e.target.checked;
                localStorage.setItem('themePreference', isLight ? 'light' : 'dark');
                if (isLight) {
                    document.body.classList.add('light-theme');
                } else {
                    document.body.classList.remove('light-theme');
                }
                if (menuThemeCheckbox) {
                    menuThemeCheckbox.checked = isLight;
                }
            });
        }
    });

    const tabFontFamilySelect = document.getElementById('tabFontFamilySelect');
    const tabFontSizeSelect = document.getElementById('tabFontSizeSelect');

    if (tabFontFamilySelect && tabFontSizeSelect) {
        const savedFontFamily = localStorage.getItem('accessibilityFontFamily') || "'Inter', sans-serif";
        const savedFontSize = localStorage.getItem('accessibilityFontSize') || "1";

        tabFontFamilySelect.value = savedFontFamily;
        tabFontSizeSelect.value = savedFontSize;

        tabFontFamilySelect.addEventListener('change', (e) => {
            const val = e.target.value;
            document.documentElement.style.setProperty('--app-font-family', val);
            localStorage.setItem('accessibilityFontFamily', val);
            const topbarFontSelect = document.getElementById('fontFamilySelect');
            if (topbarFontSelect) topbarFontSelect.value = val;
        });

        tabFontSizeSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            document.documentElement.style.setProperty('--font-scale-factor', val);
            localStorage.setItem('accessibilityFontSize', val);
            const topbarSizeSelect = document.getElementById('fontSizeSelect');
            if (topbarSizeSelect) topbarSizeSelect.value = val;
        });
    }
</script>