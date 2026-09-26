<?php
if (!defined('ALLOWED_ACCESS')) {
    require_once 'db_config.php';
    require_once 'audit_helper.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header("Location: dashboard.php?page=violation_records");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole = $_SESSION['role'] ?? 'Guard';
$currentUserId = $_SESSION['user_id'] ?? 1;
$currentRoleNorm = normalize_role_name($currentRole);

// Verify module-level RBAC access
$hasRecordAccess = has_module_access($currentRole, 'violation_records', $conn);
if (!$hasRecordAccess && !in_array($currentRoleNorm, ['superadmin', 'cod', 'cso'], true)) {
    $_SESSION['flash_message'] = "Unauthorized access to Violation Records.";
    $_SESSION['flash_type'] = "error";
    header("Location: dashboard.php?error=unauthorized");
    exit();
}

// State Machine Handling: Verify / Confirm ('For Verification' -> 'Unsettled') [CSO / Superadmin]
if (isset($_GET['verify_id'])) {
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'cso') {
        $_SESSION['flash_message'] = "Unauthorized action. CSO privileges required to verify violations.";
        $_SESSION['flash_type'] = "error";
        header("Location: dashboard.php?page=violation_records");
        exit();
    }

    $recordId = (int)$_GET['verify_id'];
    
    // Atomic precondition: ensure record exists and is currently 'For Verification'
    $checkStmt = $conn->prepare("SELECT record_id, status FROM violation_records WHERE record_id = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("i", $recordId);
        $checkStmt->execute();
        $res = $checkStmt->get_result();
        $rec = $res->fetch_assoc();
        $checkStmt->close();

        if ($rec && $rec['status'] === 'For Verification') {
            $updateStmt = $conn->prepare("UPDATE violation_records SET status = 'Unsettled' WHERE record_id = ? AND status = 'For Verification'");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $recordId);
                $updateStmt->execute();
                if ($updateStmt->affected_rows > 0) {
                    log_activity($conn, $currentUserId, 'Violation Verified', "Verified violation record ID #{$recordId}. Status updated to Unsettled.");
                    $_SESSION['flash_message'] = "Violation record #{$recordId} successfully verified and updated to Unsettled.";
                    $_SESSION['flash_type'] = "success";
                }
                $updateStmt->close();
            }
        } else {
            $_SESSION['flash_message'] = "Record cannot be verified or is not in 'For Verification' state.";
            $_SESSION['flash_type'] = "error";
        }
    }
    
    header("Location: dashboard.php?page=violation_records");
    exit();
}

// State Machine Handling: Settle Case ('Unsettled' -> 'Settled') [COD / Superadmin]
if (isset($_GET['settle_id'])) {
    if ($currentRoleNorm !== 'superadmin' && $currentRoleNorm !== 'cod') {
        $_SESSION['flash_message'] = "Unauthorized action. Committee on Discipline privileges required to settle cases.";
        $_SESSION['flash_type'] = "error";
        header("Location: dashboard.php?page=violation_records");
        exit();
    }

    $recordId = (int)$_GET['settle_id'];
    
    $checkStmt = $conn->prepare("SELECT record_id, status FROM violation_records WHERE record_id = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param("i", $recordId);
        $checkStmt->execute();
        $res = $checkStmt->get_result();
        $rec = $res->fetch_assoc();
        $checkStmt->close();

        if ($rec) {
            // STRICT STATE VALIDATION: COD is barred from modifying unverified cases
            if ($rec['status'] !== 'Unsettled') {
                $_SESSION['flash_message'] = "Invalid state transition: Only verified 'Unsettled' cases can be marked as Settled.";
                $_SESSION['flash_type'] = "error";
                header("Location: dashboard.php?page=violation_records");
                exit();
            }

            // Bound strictly to 'Unsettled' to prevent race conditions
            $updateStmt = $conn->prepare("UPDATE violation_records SET status = 'Settled', cleared_at = CURRENT_TIMESTAMP WHERE record_id = ? AND status = 'Unsettled'");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $recordId);
                $updateStmt->execute();
                if ($updateStmt->affected_rows > 0) {
                    log_activity($conn, $currentUserId, 'Violation Settled', "Settled case for violation record ID #{$recordId}. Cleared at timestamp updated.");
                    $_SESSION['flash_message'] = "Violation case #{$recordId} successfully marked as Settled.";
                    $_SESSION['flash_type'] = "success";
                }
                $updateStmt->close();
            }
        }
    }
    
    header("Location: dashboard.php?page=violation_records");
    exit();
}

$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$sortOrder = $_GET['sort'] ?? 'DESC';
$hasSearched = !empty($search) || $categoryFilter !== 'all' || $statusFilter !== 'all' || isset($_GET['search']);

$result = null;

if ($hasSearched) {
    $whereClauses = [];
    $paramTypes = '';
    $paramValues = [];

    if (!empty($search)) {
        $whereClauses[] = "(s.student_id_no LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $paramTypes .= 'sss';
        $paramValues[] = $searchTerm;
        $paramValues[] = $searchTerm;
        $paramValues[] = $searchTerm;
    }

    if ($categoryFilter !== 'all') {
        $whereClauses[] = "cd.category = ?";
        $paramTypes .= 's';
        $paramValues[] = $categoryFilter;
    }

    if ($statusFilter !== 'all') {
        $whereClauses[] = "vr.status = ?";
        $paramTypes .= 's';
        $paramValues[] = $statusFilter;
    }

    $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
    $sortDirection = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';

    $query = "SELECT vr.record_id, s.student_id_no, s.first_name, s.last_name, cd.offense, cd.category, vr.offense_count, vr.applied_sanction, vr.status, vr.incident_date 
              FROM violation_records vr
              JOIN students s ON vr.student_uid = s.student_uid
              JOIN code_of_discipline cd ON vr.offense_id = cd.offense_id
              $whereSql ORDER BY vr.incident_date $sortDirection";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($paramTypes)) {
            $stmt->bind_param($paramTypes, ...$paramValues);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
}
?>

<section class="admin-container">
    <header class="app-header">
        <h2>Violation Records</h2>
        <p>Review, filter, and manage student conduct reports.</p>
    </header>

    <article class="form-card">
        <form method="GET" action="dashboard.php" class="filter-form-fixed">
            <input type="hidden" name="page" value="violation_records">

            <div class="form-group flex-grow">
                <label for="search">Search Student</label>
                <input type="text" id="search" name="search" class="filter-input" placeholder="Student ID No. or Student Name" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>">
            </div>

            <div class="form-group">
                <label for="category">Violation Category</label>
                <select id="category" name="category" class="filter-select">
                    <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="Minor" <?php echo $categoryFilter === 'Minor' ? 'selected' : ''; ?>>Minor</option>
                    <option value="Major" <?php echo $categoryFilter === 'Major' ? 'selected' : ''; ?>>Major</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="For Verification" <?php echo $statusFilter === 'For Verification' ? 'selected' : ''; ?>>For Verification</option>
                    <option value="Unsettled" <?php echo $statusFilter === 'Unsettled' ? 'selected' : ''; ?>>Unsettled</option>
                    <option value="Settled" <?php echo $statusFilter === 'Settled' ? 'selected' : ''; ?>>Settled</option>
                </select>
            </div>

            <div class="form-group">
                <label for="sort">Sort Date</label>
                <select id="sort" name="sort" class="filter-select">
                    <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Most recent</option>
                    <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Oldest</option>
                </select>
            </div>

            <div class="form-actions-inline">
                <button type="submit" class="btn btn-primary">
                    <img src="assets/icons/outline/filter.svg" alt="Filter Icon" class="asset-icon-img">
                </button>
                <a href="dashboard.php?page=violation_records" class="btn btn-secondary">
                    <img src="assets/icons/outline/refresh.svg" alt="Reset Icon" class="asset-icon-img">
                </a>
            </div>
        </form>
    </article>

    <?php if (!$hasSearched): ?>
        <article class="form-card text-center pad-20">
            <p class="text-muted">Please enter a search term or select a filter above to view violation records.</p>
        </article>
    <?php else: ?>
        <article class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead class="table-header">
                        <tr>
                            <th>STUDENT NAME</th>
                            <th>OFFENSE</th>
                            <th>SANCTION</th>
                            <th>INCIDENT DATE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                    $status = $row['status'];
                                    $isSettled = ($status === 'Settled');
                                    $isPendingVerification = ($status === 'For Verification');

                                    if ($isSettled) {
                                        $statusClass = 'status-settled';
                                    } elseif ($isPendingVerification) {
                                        $statusClass = 'status-pending';
                                    } else {
                                        $statusClass = 'status-unsettled';
                                    }

                                    $count = (int)$row['offense_count'];
                                    $suffix = ($count == 1) ? 'st' : (($count == 2) ? 'nd' : (($count == 3) ? 'rd' : 'th'));
                                    $offenseText = $count . $suffix . ' offense';
                                ?>
                                <tr class="clickable-row" onclick="toggleDetails('details-<?php echo $row['record_id']; ?>')">
                                    <td>
                                        <strong class="student-name-link">
                                            <?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name'], ENT_QUOTES); ?>
                                        </strong>
                                        <div class="student-id-subtext"><?php echo htmlspecialchars($row['student_id_no'], ENT_QUOTES); ?></div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['offense'], ENT_QUOTES); ?></strong>
                                        <div class="offense-subtext"><?php echo $offenseText; ?></div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['applied_sanction'], ENT_QUOTES); ?></strong>
                                    </td>
                                    <td class="action-column-fit">
                                        <?php echo date('F d, Y h:i A', strtotime($row['incident_date'])); ?>
                                    </td>
                                    <td>
                                        <strong class="<?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($status, ENT_QUOTES)); ?></strong>
                                    </td>
                                </tr>

                                <tr id="details-<?php echo $row['record_id']; ?>" class="expandable-detail-row hide-element">
                                    <td colspan="5">
                                        <div class="expanded-card-wrapper">
                                            <div class="expanded-info-grid">
                                                <p><strong>Record ID:</strong> #<?php echo $row['record_id']; ?></p>
                                                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES); ?></p>
                                                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($row['student_id_no'], ENT_QUOTES); ?></p>
                                                <p><strong>Offense:</strong> <?php echo htmlspecialchars($row['offense'], ENT_QUOTES); ?> (<?php echo $offenseText; ?>)</p>
                                                <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category'] ?? 'N/A', ENT_QUOTES); ?></p>
                                                <p><strong>Applied Sanction:</strong> <?php echo htmlspecialchars($row['applied_sanction'], ENT_QUOTES); ?></p>
                                                <p><strong>Incident Date:</strong> <?php echo date('M d, Y h:i A', strtotime($row['incident_date'])); ?></p>
                                                <p><strong>Current Status:</strong> <span class="<?php echo $statusClass; ?>"><?php echo strtoupper(htmlspecialchars($status, ENT_QUOTES)); ?></span></p>
                                            </div>
                                            
                                            <div class="expanded-action-bar">
                                                <?php if ($isSettled): ?>
                                                    <button type="button" class="btn btn-deactive btn-icon-only" disabled title="Case Settled">
                                                        <img src="assets/icons/outline/check.svg" alt="Settled Icon" class="asset-icon-img">
                                                    </button>
                                                <?php elseif ($isPendingVerification): ?>
                                                    <?php if ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'cso'): ?>
                                                        <button type="button" class="btn btn-primary" onclick="confirmAction('Verify Violation', 'Verify and confirm this captured violation?', 'dashboard.php?page=violation_records&verify_id=<?php echo $row['record_id']; ?>')">
                                                           <img src="assets/icons/outline/check.svg" alt="Verify Icon" class="asset-icon-img">
                                                           <span>Confirm</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-deactive" disabled title="CSO verification required">Pending</button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if ($currentRoleNorm === 'superadmin' || $currentRoleNorm === 'cod'): ?>
                                                        <button type="button" class="btn btn-success" onclick="confirmAction('Settle Case', 'Mark this violation record as settled?', 'dashboard.php?page=violation_records&settle_id=<?php echo $row['record_id']; ?>')">
                                                           <img src="assets/icons/outline/check.svg" alt="Settle Icon" class="asset-icon-img">
                                                           <span>Update</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-deactive" disabled title="Committee on Discipline privilege required">Settle Case</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No matching violation records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</section>

<script>
function toggleDetails(rowId) {
    const detailRow = document.getElementById(rowId);
    if (detailRow) {
        detailRow.classList.toggle('hide-element');
    }
}

function confirmAction(title, message, targetUrl) {
    if (typeof showGlobalModal === 'function') {
        showGlobalModal(title, message, () => {
            window.location.href = targetUrl;
        }, 'Confirm', false);
    } else if (confirm(message)) {
        window.location.href = targetUrl;
    }
}
</script>