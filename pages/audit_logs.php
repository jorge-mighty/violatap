<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    echo "<main class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>Please log in to continue.</p></article></main>";
    exit();
}

$currentRole   = trim($_SESSION['role'] ?? '');
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$normRole      = normalize_role_name($currentRole);

// Verify module access permissions using central helper from db_config.php
$isAuditAllowed = has_module_access($currentRole, 'audit_logs', $conn);
if (!$isAuditAllowed && $normRole !== 'superadmin' && $normRole !== 'csoadmin' && $normRole !== 'cso' && $normRole !== 'codadmin' && $normRole !== 'cod') {
    echo "<main class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to view audit logs and system monitoring.</p></article></main>";
    exit();
}

// Enforce CSO restriction: CSO is strictly restricted to viewing Guard activity trails exclusively
$isCsoRole = ($normRole === 'csoadmin' || $normRole === 'cso');

$search        = trim($_GET['search'] ?? '');
$roleFilter    = $_GET['role_filter'] ?? 'all';
$actionFilter  = $_GET['action_filter'] ?? 'all';
$sortOrder     = strtoupper(trim($_GET['sort'] ?? 'DESC'));
$sortDirection = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';

$whereClauses = [];
$bindTypes    = "";
$bindParams   = [];

// CSO constraint enforced at SQL query level
if ($isCsoRole) {
    $whereClauses[] = "u.role = 'Guard'";
} elseif ($roleFilter !== 'all') {
    $whereClauses[] = "u.role = ?";
    $bindTypes   .= "s";
    $bindParams[] = $roleFilter;
}

if (!empty($search)) {
    $whereClauses[] = "(u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR sal.action LIKE ? OR sal.details LIKE ? OR sal.ip_address LIKE ?)";
    $bindTypes .= "sssssss";
    $searchPattern = '%' . $search . '%';
    for ($i = 0; $i < 7; $i++) {
        $bindParams[] = $searchPattern;
    }
}

if ($actionFilter !== 'all') {
    $whereClauses[] = "sal.action = ?";
    $bindTypes   .= "s";
    $bindParams[] = $actionFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$query = "SELECT sal.log_id, sal.timestamp, u.username, u.first_name, u.last_name, u.role, sal.action, sal.details, sal.ip_address 
          FROM system_audit_logs sal 
          JOIN users u ON sal.user_id = u.user_id 
          $whereSql 
          ORDER BY sal.timestamp $sortDirection LIMIT 200";

$stmtLogs = $conn->prepare($query);
$logsResult = null;

if ($stmtLogs) {
    if (!empty($bindTypes)) {
        $stmtLogs->bind_param($bindTypes, ...$bindParams);
    }
    $stmtLogs->execute();
    $logsResult = $stmtLogs->get_result();
}

$actionsQuery = $conn->query("SELECT DISTINCT action FROM system_audit_logs ORDER BY action ASC");
?>

<main class="admin-container">
    <header class="app-header page-title-header">
        <h2 class="page-main-title">Audit Logs & System Monitoring</h2>
        <p class="page-subtitle">Track system activity, administrative actions, and user security events.</p>
    </header>

    <section class="audit-filter-section">
        <article class="form-card">
            <form method="GET" action="dashboard.php" class="filter-form-fixed">
                <input type="hidden" name="page" value="audit_logs">

                <div class="form-group flex-grow">
                    <label for="search">Search Logs</label>
                    <input type="text" id="search" name="search" class="filter-input" placeholder="Keyword, name, or details..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8', false); ?>">
                </div>

                <?php if (!$isCsoRole): ?>
                <div class="form-group">
                    <label for="role_filter">Role</label>
                    <select id="role_filter" name="role_filter" class="filter-select">
                        <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="Superadmin" <?php echo $roleFilter === 'Superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                        <option value="COD Admin" <?php echo $roleFilter === 'COD Admin' ? 'selected' : ''; ?>>COD Admin</option>
                        <option value="CSO Admin" <?php echo $roleFilter === 'CSO Admin' ? 'selected' : ''; ?>>CSO Admin</option>
                        <option value="Guard" <?php echo $roleFilter === 'Guard' ? 'selected' : ''; ?>>Guard</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="action_filter">Action Type</label>
                    <select id="action_filter" name="action_filter" class="filter-select">
                        <option value="all">All</option>
                        <?php if ($actionsQuery && $actionsQuery->num_rows > 0): ?>
                            <?php while ($act = $actionsQuery->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($act['action'], ENT_QUOTES, 'UTF-8', false); ?>" <?php echo $actionFilter === $act['action'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($act['action'], ENT_QUOTES, 'UTF-8', false); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sort">Sort Timestamp</label>
                    <select id="sort" name="sort" class="filter-select">
                        <option value="DESC" <?php echo $sortDirection === 'DESC' ? 'selected' : ''; ?>>Most Recent</option>
                        <option value="ASC" <?php echo $sortDirection === 'ASC' ? 'selected' : ''; ?>>Oldest</option>
                    </select>
                </div>

                <div class="form-actions-inline">
                    <button type="submit" class="btn btn-primary">
                        <img src="assets/icons/outline/filter.svg" alt="Filter Icon" class="asset-icon-img">
                    </button>
                    <a href="dashboard.php?page=audit_logs" class="btn btn-secondary">
                        <img src="assets/icons/outline/refresh.svg" alt="Reset Icon" class="asset-icon-img">
                    </a>
                </div>
            </form>
        </article>
    </section>

    <section class="audit-data-section">
        <article class="table-card">
            <header class="app-header table-card-header">
                <h3>Activity Trail <?php echo $isCsoRole ? '(Guard Activity Only)' : '(Showing latest records)'; ?></h3>
            </header>

            <div class="table-responsive audit-table-wrapper">
                <table class="data-table audit-data-table">
                    <thead class="table-header">
                        <tr>
                            <th>TIMESTAMP</th>
                            <th>DETAILS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logsResult && $logsResult->num_rows > 0): ?>
                            <?php while ($row = $logsResult->fetch_assoc()): ?>
                                <?php 
                                    $r = $row['role'];
                                    $roleBadgeClass = 'status-settled';
                                    if ($r === 'Guard') {
                                        $roleBadgeClass = 'status-pending';
                                    } elseif ($r === 'Superadmin' || $r === 'CSO Admin' || $r === 'COD Admin') {
                                        $roleBadgeClass = 'status-unsettled';
                                    }

                                    $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                                    if (empty($fullName)) {
                                        $fullName = $row['username'] ?? 'System User';
                                    }

                                    // Normalize pre-existing entities before string substitution
                                    $rawDetails  = htmlspecialchars_decode($row['details'] ?? '', ENT_QUOTES);
                                    $rawUsername = htmlspecialchars_decode($row['username'] ?? '', ENT_QUOTES);
                                    $rawFullName = htmlspecialchars_decode($fullName, ENT_QUOTES);

                                    if (!empty($rawUsername)) {
                                        $rawDetails = str_replace($rawUsername, $rawFullName, $rawDetails);
                                    }

                                    // Encode with double_encode = false to prevent literal entity rendering
                                    $formattedDetails = htmlspecialchars($rawDetails, ENT_QUOTES, 'UTF-8', false);

                                    $detailsId = "details-" . (int)$row['log_id'];
                                ?>
                                <tr class="clickable-row" onclick="toggleDetails('<?php echo $detailsId; ?>')">
                                    <td class="action-column-fit"><?php echo date('M d, Y h:i:s A', strtotime($row['timestamp'])); ?></td>
                                    <td><strong class="student-name-link"><?php echo $formattedDetails; ?></strong></td>
                                </tr>

                                <tr id="<?php echo $detailsId; ?>" class="expandable-detail-row hide-element">
                                    <td colspan="2">
                                        <div class="expanded-card-wrapper">
                                            <div class="expanded-info-grid">
                                                <p><strong>Log ID:</strong> <code class="code-box"><?php echo (int)$row['log_id']; ?></code></p>
                                                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($rawFullName, ENT_QUOTES, 'UTF-8', false); ?></p>
                                                <p><strong>Username:</strong> <em><?php echo htmlspecialchars($rawUsername, ENT_QUOTES, 'UTF-8', false); ?></em></p>
                                                <p><strong>Role:</strong> <span class="<?php echo $roleBadgeClass; ?>"><?php echo strtoupper(htmlspecialchars($r ?? '', ENT_QUOTES, 'UTF-8', false)); ?></span></p>
                                                <p><strong>Action Type:</strong> <code class="code-box"><?php echo htmlspecialchars($row['action'] ?? '', ENT_QUOTES, 'UTF-8', false); ?></code></p>
                                                <p><strong>IP Address:</strong> <small class="text-muted-sm"><?php echo htmlspecialchars($row['ip_address'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8', false); ?></small></p>
                                                <p><strong>Timestamp:</strong> <?php echo date('F d, Y h:i:s A', strtotime($row['timestamp'])); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center">No audit log records found matching your filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</main>

<script>
function toggleDetails(rowId) {
    const detailRow = document.getElementById(rowId);
    if (detailRow) {
        detailRow.classList.toggle('hide-element');
    }
}
</script>