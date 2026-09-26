<?php
if (!defined('ALLOWED_ACCESS')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header("Location: dashboard.php?page=user_management");
    exit();
}

$currentRole   = $_SESSION['role'] ?? 'Guard';
$currentUserId = $_SESSION['user_id'] ?? 0;
$currentRoleNorm = normalize_role_name($currentRole);

// Centralized dynamic RBAC check
$isUserMgmtAllowed = has_module_access($currentRole, 'user_management', $conn);
if (!$isUserMgmtAllowed && !in_array($currentRoleNorm, ['superadmin', 'cso', 'cod'], true)) {
    echo "<main class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to access system user credentials and account management.</p></article></main>";
    exit();
}

$feedbackMsg = '';
$feedbackType = 'Notice';

if (isset($_GET['error'])) {
    $feedbackMsg = $_GET['error'];
    $feedbackType = 'Error';
} elseif (isset($_GET['msg'])) {
    $feedbackMsg = $_GET['msg'];
    $feedbackType = 'Success';
}

$tempToastTitle = $_SESSION['temp_toast_title'] ?? '';
$tempToastMsg   = $_SESSION['temp_toast_msg'] ?? '';
unset($_SESSION['temp_toast_title'], $_SESSION['temp_toast_msg']);

$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_archived'");
$hasArchivedCol = ($colCheck && $colCheck->num_rows > 0);

// Action: Archive / Delete User
if (isset($_POST['delete_user'])) {
    $uid = (int)$_POST['target_user_id'];
    $targetQuery = $conn->prepare("SELECT role, username FROM users WHERE user_id = ? LIMIT 1");
    if ($targetQuery) {
        $targetQuery->bind_param("i", $uid);
        $targetQuery->execute();
        $targetRes = $targetQuery->get_result();
        $targetData = $targetRes->fetch_assoc();
        $targetQuery->close();

        if ($targetData) {
            $targetRoleNorm = normalize_role_name($targetData['role']);

            if ($targetRoleNorm === 'superadmin') {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: Superadmin accounts cannot be archived or deleted.'));
                exit();
            }

            if ($currentRoleNorm === 'cso' && $targetRoleNorm !== 'guard') {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: CSO users can only archive Guard accounts.'));
                exit();
            }

            if ($currentRoleNorm === 'cod' && !in_array($targetRoleNorm, ['cod', 'cso'], true)) {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: COD users can only archive COD and CSO accounts.'));
                exit();
            }

            if ($hasArchivedCol) {
                $stmt = $conn->prepare("UPDATE users SET is_archived = 1 WHERE user_id = ?");
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            }

            if ($stmt) {
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $stmt->close();
                log_activity($conn, $currentUserId, 'User Archived', "Archived account for user ID #{$uid} ({$targetData['username']}).");
                header("Location: dashboard.php?page=user_management&msg=" . urlencode('User archived successfully'));
                exit();
            }
        }
    }
}

// Action: Restore User
if (isset($_GET['restore_id'])) {
    $uid = (int)$_GET['restore_id'];
    $targetQuery = $conn->prepare("SELECT role, username FROM users WHERE user_id = ? LIMIT 1");
    if ($targetQuery) {
        $targetQuery->bind_param("i", $uid);
        $targetQuery->execute();
        $targetRes = $targetQuery->get_result();
        $targetData = $targetRes->fetch_assoc();
        $targetQuery->close();

        if ($targetData) {
            $targetRoleNorm = normalize_role_name($targetData['role']);

            if ($currentRoleNorm === 'cso' && $targetRoleNorm !== 'guard') {
                header("Location: dashboard.php?page=user_management&view=archived&error=" . urlencode('Unauthorized: CSO users can only restore Guard accounts.'));
                exit();
            }

            if ($currentRoleNorm === 'cod' && !in_array($targetRoleNorm, ['cod', 'cso'], true)) {
                header("Location: dashboard.php?page=user_management&view=archived&error=" . urlencode('Unauthorized: COD users can only restore COD and CSO accounts.'));
                exit();
            }

            if ($hasArchivedCol) {
                $stmt = $conn->prepare("UPDATE users SET is_archived = 0 WHERE user_id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $uid);
                    $stmt->execute();
                    $stmt->close();
                    log_activity($conn, $currentUserId, 'User Restored', "Restored archived account for user ID #{$uid} ({$targetData['username']}).");
                    header("Location: dashboard.php?page=user_management&view=archived&msg=" . urlencode('User restored successfully'));
                    exit();
                }
            }
        }
    }
}

// Action: Reset Password
if (isset($_POST['reset_password'])) {
    $uid = (int)$_POST['target_user_id'];
    $targetQuery = $conn->prepare("SELECT last_name, school_id_no, role, username FROM users WHERE user_id = ? LIMIT 1");
    if ($targetQuery) {
        $targetQuery->bind_param("i", $uid);
        $targetQuery->execute();
        $targetRes = $targetQuery->get_result();
        $udata = $targetRes->fetch_assoc();
        $targetQuery->close();

        if ($udata) {
            $targetRoleNorm = normalize_role_name($udata['role']);

            if ($targetRoleNorm === 'superadmin' && $currentRoleNorm !== 'superadmin') {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: Cannot reset Superadmin credentials.'));
                exit();
            }

            if ($currentRoleNorm === 'cso' && $targetRoleNorm !== 'guard') {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: CSO users can only reset Guard passwords.'));
                exit();
            }

            if ($currentRoleNorm === 'cod' && !in_array($targetRoleNorm, ['cod', 'cso'], true)) {
                header("Location: dashboard.php?page=user_management&error=" . urlencode('Unauthorized: COD users can only reset COD and CSO passwords.'));
                exit();
            }

            $idParts = explode('-', $udata['school_id_no']);
            $middle = isset($idParts[1]) ? $idParts[1] : '1234';
            $defaultPlain = strtolower(str_replace(' ', '', $udata['last_name'])) . str_replace(' ', '', $middle);
            
            $hashed = password_hash($defaultPlain, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if ($update) {
                $update->bind_param("si", $hashed, $uid);
                $update->execute();
                $update->close();

                log_activity($conn, $currentUserId, 'Password Reset', "Reset password credentials for user ID #{$uid} ({$udata['username']}).");
                
                $_SESSION['temp_toast_title'] = "Password Reset Successful";
                $_SESSION['temp_toast_msg']   = "Temp Password for {$udata['username']}: <br><code class='code-box'>" . htmlspecialchars($defaultPlain) . "</code>";

                header("Location: dashboard.php?page=user_management");
                exit();
            }
        }
    }
}

$viewArchived = isset($_GET['view']) && $_GET['view'] === 'archived' ? 1 : 0;
$whereClauses = [];

if ($hasArchivedCol) {
    $whereClauses[] = "u.is_archived = {$viewArchived}";
}

if ($currentRoleNorm === 'cso') {
    $whereClauses[] = "LOWER(REPLACE(REPLACE(u.role, ' ', ''), '_', '')) = 'guard'";
} elseif ($currentRoleNorm !== 'superadmin') {
    $whereClauses[] = "LOWER(REPLACE(REPLACE(u.role, ' ', ''), '_', '')) != 'superadmin'";
}

$whereFilter = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$userCountQuery = $conn->query("SELECT COUNT(*) as c FROM users u $whereFilter");
$totalUsers = $userCountQuery ? $userCountQuery->fetch_assoc()['c'] : 0;

$dbRoles = ['Guard', 'COD', 'CSO', 'Superadmin'];
?>

<div id="toastFloatingContainer" class="toast-floating-wrapper"></div>

<section class="admin-container">
    <header class="app-header header-flex-aligned">
        <div>
            <h2>User Management</h2>
            <p><?php echo ($currentRoleNorm === 'cso') ? 'Manage guard credentials and accounts.' : 'Manage credentials and system access.'; ?></p>
        </div>
        <div class="header-actions-inline nav-align-right">
            <?php if ($viewArchived): ?>
                <a href="dashboard.php?page=user_management" class="btn btn-primary">
                    <img src="assets/icons/outline/arrow-narrow-left.svg" alt="Back Icon" class="asset-icon-img">
                    <span title="View Active Users">Active Users</span>
                </a>
            <?php else: ?>
                <?php if ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'cso' || $currentRoleNorm === 'cod'): ?>
                    <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                        <img src="assets/icons/outline/plus.svg" alt="Add Icon" class="asset-icon-img">
                        <span title="Add New User">Add</span>
                    </button>
                <?php endif; ?>
                <a href="dashboard.php?page=user_management&view=archived" class="btn btn-secondary">
                    <img src="assets/icons/outline/archive.svg" alt="Archive Icon" class="asset-icon-img">
                    <span title="View Archived User">Archive</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <article class="table-card">
        <header class="app-header header-flex gap-margin-bottom">
            <h3><?php echo $viewArchived ? 'Archived System Users' : 'Active System Users'; ?> (<span id="userCountDisplay"><?php echo $totalUsers; ?></span>)</h3>
        </header>

        <div class="filter-form-fixed">
            <div class="form-group flex-grow">
                <label for="searchUser">Search User</label>
                <input type="text" id="searchUser" class="filter-input" placeholder="<?php echo ($currentRoleNorm === 'cso') ? 'Search Guard Name, ID, or Username...' : 'Search Name, ID, or Username...'; ?>" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="filterStatus">Status</label>
                <select id="filterStatus" class="filter-select">
                    <option value="all">All</option>
                    <option value="active" selected>Active</option>
                    <option value="Online">Online</option>
                    <option value="Idle">Idle</option>
                    <option value="Offline">Offline</option>
                </select>
            </div>

            <div class="form-group">
                <label for="filterRole">Role</label>
                <select id="filterRole" class="filter-select">
                    <?php if ($currentRoleNorm === 'cso'): ?>
                        <option value="all" selected>All Guards</option>
                    <?php else: ?>
                        <option value="all" selected>All</option>
                        <?php foreach ($dbRoles as $drole): ?>
                            <?php if ($currentRoleNorm !== 'superadmin' && normalize_role_name($drole) === 'superadmin') continue; ?>
                            <option value="<?php echo htmlspecialchars($drole); ?>"><?php echo htmlspecialchars($drole); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sortUser">Sort Name</label>
                <select id="sortUser" class="filter-select">
                    <option value="ASC" selected>Name (A – Z)</option>
                    <option value="DESC">Name (Z – A)</option>
                </select>
            </div>

            <div class="form-actions-inline">
                <button type="button" class="btn btn-primary" onclick="applyUserTableFilters()">
                    <img src="assets/icons/outline/filter.svg" alt="Filter Icon" class="asset-icon-img">
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetUserTableFilters()">
                    <img src="assets/icons/outline/refresh.svg" alt="Reset Icon" class="asset-icon-img">
                </button>
            </div>
        </div>

        <div class="table-responsive table-scroll-580">
            <table class="data-table user-table-custom">
                <thead class="table-header">
                    <tr>
                        <th class="col-fullname">Full Name</th>
                        <th class="col-username">Username</th>
                        <th class="col-role">Role</th>
                        <th class="col-password">Security</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php
                    $query = "SELECT u.user_id, u.username, u.first_name, u.last_name, u.school_id_no, u.role, 
                                     MAX(sal.timestamp) AS last_log_time,
                                     CASE 
                                         WHEN MAX(sal.timestamp) >= (NOW() - INTERVAL 5 MINUTE) THEN 'Online'
                                         WHEN MAX(sal.timestamp) IS NOT NULL AND MAX(sal.timestamp) >= (NOW() - INTERVAL 30 MINUTE) THEN 'Idle'
                                         ELSE 'Offline'
                                     END AS computed_status
                              FROM users u 
                              LEFT JOIN system_audit_logs sal ON sal.user_id = u.user_id 
                              $whereFilter 
                              GROUP BY u.user_id 
                              ORDER BY u.last_name ASC";
                    $users = $conn->query($query);
                    
                    if($users && $users->num_rows > 0):
                        while($u = $users->fetch_assoc()):
                            $targetRoleNorm = normalize_role_name($u['role']);
                            $isRoot = ($targetRoleNorm === 'superadmin');
                            $userRole = !empty($u['role']) ? $u['role'] : 'Unassigned';
                            $computedStatus = $u['computed_status'];
                            
                            $dotClass = 'offline';
                            if ($computedStatus === 'Online') {
                                $dotClass = 'online';
                            } elseif ($computedStatus === 'Idle') {
                                $dotClass = 'idle';
                            }
                            
                            $canManageThisUser = ($currentRoleNorm === 'superadmin') || 
                                                 (($currentRoleNorm === 'cso') && $targetRoleNorm === 'guard') ||
                                                 (($currentRoleNorm === 'cod') && in_array($targetRoleNorm, ['cod', 'cso'], true));

                            $roleClass = 'status-settled';
                            if ($targetRoleNorm === 'guard') {
                                $roleClass = 'status-pending';
                            } elseif ($isRoot || $targetRoleNorm === 'cso') {
                                $roleClass = 'status-unsettled';
                            }
                    ?>
                    <tr data-role="<?php echo htmlspecialchars($userRole); ?>" 
                        data-role-norm="<?php echo htmlspecialchars($targetRoleNorm); ?>"
                        data-name="<?php echo htmlspecialchars(strtolower(($u['last_name'] ?? '') . ', ' . ($u['first_name'] ?? ''))); ?>"
                        data-status="<?php echo htmlspecialchars($computedStatus); ?>">
                        <td class="col-fullname">
                            <span class="status-dot <?php echo $dotClass; ?>" title="<?php echo htmlspecialchars($computedStatus); ?>"></span>
                            <span class="student-name-lg">
                                <?php echo htmlspecialchars(($u['last_name'] ?? 'System') . ($u['first_name'] ? ', ' . $u['first_name'] : '')); ?>
                            </span><br>
                            <small class="text-muted-sm"><strong class="text-status-label"><?php echo $computedStatus; ?></strong><?php echo !empty($u['school_id_no']) ? ' • ' . htmlspecialchars($u['school_id_no']) : ''; ?></small>
                        </td>
                        <td class="col-username">
                            <code class="code-box"><?php echo htmlspecialchars($u['username']); ?></code>
                        </td>
                        <td class="col-role">
                            <span class="<?php echo $roleClass; ?>"><?php echo htmlspecialchars($userRole); ?></span>
                        </td>
                        <td class="col-password">
                            <span class="text-muted-sm">Hidden (Encrypted)</span>
                        </td>
                        <td class="col-actions">
                            <?php if(!$isRoot): ?>
                                <?php if ($canManageThisUser): ?>
                                    <div class="action-buttons-flex">
                                        <?php if ($viewArchived): ?>
                                            <a href="dashboard.php?page=user_management&view=archived&restore_id=<?php echo $u['user_id']; ?>" class="btn btn-primary btn-icon-only" title="Restore User" onclick="event.preventDefault(); start5SecUndo('Restoring account for <?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>...', () => { window.location.href = this.href; });">
                                                <img src="assets/icons/outline/restore.svg" alt="Restore Icon" class="asset-icon-img">
                                            </a>
                                        <?php else: ?>
                                            <form method="POST" id="reset_form_<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="reset_password" value="1">
                                                <button type="button" class="btn btn-secondary btn-icon-only" title="Reset Password" onclick="start5SecUndo('Resetting password for <?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>...', () => { document.getElementById('reset_form_<?php echo $u['user_id']; ?>').submit(); })">
                                                    <img src="assets/icons/outline/refresh.svg" alt="Reset Password Icon" class="asset-icon-img">
                                                </button>
                                            </form>

                                            <form method="POST" id="archive_form_<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['user_id']; ?>">
                                                <input type="hidden" name="delete_user" value="1">
                                                <button type="button" class="btn btn-warning-archive btn-icon-only" title="Archive User" onclick="start5SecUndo('Archiving account for <?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>...', () => { document.getElementById('archive_form_<?php echo $u['user_id']; ?>').submit(); })">
                                                    <img src="assets/icons/outline/archive.svg" alt="Archive Icon" class="asset-icon-img">
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn btn-deactive btn-icon-only" disabled title="Authorization required">
                                        <img src="assets/icons/outline/lock.svg" alt="Protected Icon" class="asset-icon-img">
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <small class="text-italic-muted">Protected</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No <?php echo $viewArchived ? 'archived' : 'active'; ?> <?php echo ($currentRoleNorm === 'cso') ? 'guard' : 'system user'; ?> records found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<!-- Custom Alert Modal Window -->
<div id="customAlertModal" class="modal-backdrop-overlay hide-element">
    <div class="custom-modal-card alert-modal-card">
        <div class="custom-modal-header">
            <strong id="customAlertTitle">Notification</strong>
            <button type="button" class="custom-modal-close" onclick="closeCustomAlertModal()">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p id="customAlertMessage"></p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeCustomAlertModal()">
                <img src="assets/icons/outline/check.svg" alt="OK Icon" class="asset-icon-img">
                <span>OK</span>
            </button>
        </div>
    </div>
</div>

<?php if ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'cso' || $currentRoleNorm === 'cod'): ?>
<div id="addUserModal" class="modal-backdrop-overlay hide-element">
    <div class="custom-modal-card">
        <div class="custom-modal-header">
            <strong>Register New User</strong>
            <button type="button" class="custom-modal-close" onclick="closeAddUserModal()">&times;</button>
        </div>

        <form method="POST" action="add_user_process.php" class="vertical-form">
            <div class="custom-modal-body">
                <div class="form-row-2col">
                    <div class="form-group-vertical">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="filter-input" placeholder="First Name" required>
                    </div>
                    <div class="form-group-vertical">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="filter-input" placeholder="Last Name" required oninput="updateAutoUserFields()">
                    </div>
                </div>

                <div class="form-group-vertical">
                    <label for="school_id_no">ID Number</label>
                    <input type="text" id="school_id_no" name="school_id_no" class="filter-input" placeholder="2023-0366-M" required oninput="updateAutoUserFields()">
                </div>

                <div class="form-group-vertical">
                    <label for="username">Generated Username</label>
                    <input type="text" id="username" name="username" class="filter-input glass-readonly-input" placeholder="Auto-generated username" readonly required autocomplete="off">
                </div>

                <div class="form-group-vertical">
                    <label for="role">User Role</label>
                    <select id="role" name="role" class="filter-select" required onchange="updateAutoUserFields()">
                        <?php 
                        foreach ($dbRoles as $drole) {
                            $normDrole = normalize_role_name($drole);
                            if ($currentRoleNorm !== 'superadmin' && $normDrole === 'superadmin') continue;
                            if ($currentRoleNorm === 'cso' && $normDrole !== 'guard') continue;
                            if ($currentRoleNorm === 'cod' && !in_array($normDrole, ['guard', 'cso', 'cod'], true)) continue;

                            $isSelected = ($normDrole === 'guard') ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($drole) . "\" {$isSelected}>" . htmlspecialchars($drole) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <input type="hidden" id="auto_password" name="password">
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">
                    <img src="assets/icons/outline/x.svg" alt="Cancel Icon" class="asset-icon-img">
                    <span>Cancel</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <img src="assets/icons/outline/user-plus.svg" alt="Add Icon" class="asset-icon-img">
                    <span>Create Account</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
let pendingActionTimer = null;
let activeToastElement = null;

function showCustomAlert(title, message) {
    const modal = document.getElementById('customAlertModal');
    const titleEl = document.getElementById('customAlertTitle');
    const msgEl = document.getElementById('customAlertMessage');

    if (!modal || !titleEl || !msgEl) return;

    titleEl.innerText = title || 'Notification';
    msgEl.innerText = message || '';
    modal.classList.remove('hide-element');
    modal.classList.add('active');
}

function closeCustomAlertModal() {
    const modal = document.getElementById('customAlertModal');
    if (modal) {
        modal.classList.add('hide-element');
        modal.classList.remove('active');
    }
}

function start5SecUndo(message, onExecute) {
    cancelPendingUndo();

    const container = document.getElementById('toastFloatingContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast-undo-notification';

    const content = document.createElement('div');
    content.className = 'toast-undo-content';

    const icon = document.createElement('img');
    icon.src = 'assets/icons/outline/archive.svg';
    icon.alt = 'Action Icon';
    icon.className = 'asset-icon-img';

    const text = document.createElement('span');
    text.className = 'toast-undo-text';
    text.innerText = message;

    content.appendChild(icon);
    content.appendChild(text);

    const undoBtn = document.createElement('button');
    undoBtn.type = 'button';
    undoBtn.className = 'btn btn-secondary toast-undo-action-btn';
    undoBtn.innerText = 'Undo';
    undoBtn.onclick = function() {
        cancelPendingUndo();
    };

    const progressBar = document.createElement('div');
    progressBar.className = 'toast-countdown-progress';

    toast.appendChild(content);
    toast.appendChild(undoBtn);
    toast.appendChild(progressBar);

    container.appendChild(toast);
    activeToastElement = toast;

    pendingActionTimer = setTimeout(() => {
        if (activeToastElement) {
            activeToastElement.remove();
            activeToastElement = null;
        }
        pendingActionTimer = null;
        onExecute();
    }, 5000);
}

function cancelPendingUndo() {
    if (pendingActionTimer) {
        clearTimeout(pendingActionTimer);
        pendingActionTimer = null;
    }
    if (activeToastElement) {
        activeToastElement.classList.add('toast-hiding');
        setTimeout(() => {
            if (activeToastElement && activeToastElement.parentNode) {
                activeToastElement.parentNode.removeChild(activeToastElement);
            }
            activeToastElement = null;
        }, 300);
    }
}

function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if(modal) {
        modal.classList.remove('hide-element');
        modal.classList.add('active');
        updateAutoUserFields();
    }
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if(modal) {
        modal.classList.add('hide-element');
        modal.classList.remove('active');
    }
}

function updateAutoUserFields() {
    const lastNameInput = document.getElementById('last_name');
    const roleSelect = document.getElementById('role');
    const schoolIdInput = document.getElementById('school_id_no');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('auto_password');

    if (!lastNameInput || !roleSelect || !usernameInput) return;

    let lastName = lastNameInput.value.trim();
    let role = roleSelect.value;
    let schoolId = schoolIdInput ? schoolIdInput.value.trim() : '';

    if (role === 'Guard') {
        let baseName = lastName.replace(/^sg\s+/i, '').trim();
        if (baseName !== '') {
            lastName = 'SG ' + baseName;
            lastNameInput.value = lastName;
        }
    } else {
        if (/^sg\s+/i.test(lastName)) {
            lastName = lastName.replace(/^sg\s+/i, '').trim();
            lastNameInput.value = lastName;
        }
    }

    let cleanLastName = lastName.replace(/\s+/g, '').toLowerCase();
    let cleanRole = role.replace(/\s+/g, '').toLowerCase();
    let autoUsername = cleanLastName + cleanRole;
    usernameInput.value = autoUsername;

    let idParts = schoolId.split('-');
    let middleDigits = (idParts.length > 1 && idParts[1]) ? idParts[1].replace(/\s+/g, '') : '1234';
    let autoPassword = (cleanLastName + middleDigits).toLowerCase();
    if (passwordInput) passwordInput.value = autoPassword;
}

function applyUserTableFilters() {
    const searchInput = document.getElementById('searchUser');
    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusVal = document.getElementById('filterStatus').value;
    const roleVal = document.getElementById('filterRole').value;
    const sortVal = document.getElementById('sortUser').value;

    const tbody = document.getElementById('userTableBody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr[data-role]'));
    let visibleCount = 0;

    rows.forEach(row => {
        const rRole = row.getAttribute('data-role');
        const rRoleNorm = row.getAttribute('data-role-norm');
        const rStatus = row.getAttribute('data-status');
        const rText = row.innerText.toLowerCase();

        const matchesSearch = searchVal === '' || rText.includes(searchVal);
        
        let matchesStatus = false;
        if (statusVal === 'all') {
            matchesStatus = true;
        } else if (statusVal === 'active') {
            matchesStatus = (rStatus === 'Online' || rStatus === 'Idle');
        } else {
            matchesStatus = (rStatus === statusVal);
        }

        let matchesRole = false;
        if (roleVal === 'all') {
            matchesRole = true;
        } else {
            matchesRole = (rRole === roleVal || rRoleNorm === roleVal.toLowerCase().replace(/[\s_-]+/g, ''));
        }

        if (matchesSearch && matchesStatus && matchesRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    rows.sort((a, b) => {
        const nameA = a.getAttribute('data-name');
        const nameB = b.getAttribute('data-name');
        return (sortVal === 'ASC') ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
    });

    rows.forEach(row => tbody.appendChild(row));
    const countDisplay = document.getElementById('userCountDisplay');
    if (countDisplay) countDisplay.innerText = visibleCount;
}

function resetUserTableFilters() {
    const searchInput = document.getElementById('searchUser');
    const statusSelect = document.getElementById('filterStatus');
    const roleSelect = document.getElementById('filterRole');
    const sortSelect = document.getElementById('sortUser');

    if (searchInput) searchInput.value = '';
    if (statusSelect) statusSelect.value = 'active';
    if (roleSelect) roleSelect.value = 'all';
    if (sortSelect) sortSelect.value = 'ASC';

    applyUserTableFilters();
}

document.getElementById('searchUser').addEventListener('keyup', applyUserTableFilters);
document.getElementById('filterStatus').addEventListener('change', applyUserTableFilters);
document.getElementById('filterRole').addEventListener('change', applyUserTableFilters);
document.getElementById('sortUser').addEventListener('change', applyUserTableFilters);

document.addEventListener('DOMContentLoaded', () => {
    applyUserTableFilters();
    updateAutoUserFields();

    <?php if (!empty($feedbackMsg)): ?>
        showCustomAlert('<?php echo htmlspecialchars($feedbackType, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($feedbackMsg, ENT_QUOTES); ?>');
    <?php endif; ?>

    <?php if (!empty($tempToastTitle)): ?>
        if (typeof showToast === 'function') {
            showToast('<?php echo addslashes($tempToastTitle); ?>', '<?php echo addslashes($tempToastMsg); ?>', 'info', 15000);
        }
    <?php endif; ?>
});
</script>