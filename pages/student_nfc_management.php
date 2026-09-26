<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    echo "<section class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>Please log in to continue.</p></article></section>";
    exit();
}

$currentRole   = trim($_SESSION['role'] ?? '');
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$normRole      = normalize_role_name($currentRole);

// Dynamic Database-Driven RBAC Verification
if (!has_module_access($currentRole, 'student_nfc_management', $conn)) {
    echo "<section class='admin-container'><article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to access Student & NFC Management.</p></article></section>";
    exit();
}

// -------------------------------------------------------------------------
// 1. BACKEND ACTION HANDLERS
// -------------------------------------------------------------------------

// Audit View Details AJAX Logger
if (isset($_GET['log_view']) && isset($_GET['student_uid'])) {
    $studentUid = (int)$_GET['student_uid'];
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'View Details', ?, ?)");
    $details = "Viewed detailed operations profile for student ID: $studentUid.";
    $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
    $auditStmt->execute();
    $auditStmt->close();
    exit();
}

// Action: Save / Update Student Record (Optionally includes immediate NFC UID linking)
if (isset($_POST['save_student_record'])) {
    $studentUid  = $_POST['student_uid'] ?? '';
    $studentIdNo = trim($_POST['student_id_no'] ?? $_POST['school_id_no'] ?? '');
    $firstName   = trim($_POST['first_name'] ?? '');
    $middlename  = trim($_POST['middlename'] ?? '');
    $lastName    = trim($_POST['last_name'] ?? '');
    $department  = trim($_POST['department'] ?? '');
    $course      = trim($_POST['course'] ?? '');
    $major       = trim($_POST['major'] ?? '');
    $yearLevel   = (int)($_POST['year_level'] ?? 1);
    $section     = trim($_POST['section'] ?? '');
    $nfcUid      = trim($_POST['nfc_uid'] ?? '');

    $targetStudentUid = null;
    $isNewStudent     = false;

    if (!empty($studentUid)) {
        // Update Existing Student
        $targetStudentUid = (int)$studentUid;
        $updateStmt = $conn->prepare("UPDATE students SET student_id_no = ?, first_name = ?, middlename = ?, last_name = ?, department = ?, course = ?, major = ?, year_level = ?, section = ? WHERE student_uid = ?");
        $updateStmt->bind_param("sssssssssi", $studentIdNo, $firstName, $middlename, $lastName, $department, $course, $major, $yearLevel, $section, $targetStudentUid);
        if ($updateStmt->execute()) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Student Updated', ?, ?)");
            $details = "Updated student details for ID: $targetStudentUid ($firstName $lastName).";
            $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
            $auditStmt->execute();
            $auditStmt->close();
        }
        $updateStmt->close();
    } else {
        // Insert New Student
        $isNewStudent = true;
        $insertStmt = $conn->prepare("INSERT INTO students (student_id_no, first_name, middlename, last_name, department, course, major, year_level, section, is_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $insertStmt->bind_param("sssssssss", $studentIdNo, $firstName, $middlename, $lastName, $department, $course, $major, $yearLevel, $section);
        if ($insertStmt->execute()) {
            $targetStudentUid = $conn->insert_id;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Student Added', ?, ?)");
            $details = "Added new student: $firstName $lastName (Student ID: $studentIdNo).";
            $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
            $auditStmt->execute();
            $auditStmt->close();
        }
        $insertStmt->close();
    }

    $cardLinked = false;
    // Process NFC Card Assignment if provided
    if ($targetStudentUid && !empty($nfcUid)) {
        $checkNfc = $conn->prepare("SELECT card_id FROM nfc_cards WHERE nfc_uid = ?");
        $checkNfc->bind_param("s", $nfcUid);
        $checkNfc->execute();
        $resNfc = $checkNfc->get_result();

        if ($resNfc && $resNfc->num_rows > 0) {
            $card = $resNfc->fetch_assoc();
            $cardId = $card['card_id'];
            $linkStmt = $conn->prepare("UPDATE nfc_cards SET student_uid = ?, is_active = 1 WHERE card_id = ?");
            $linkStmt->bind_param("ii", $targetStudentUid, $cardId);
            $linkStmt->execute();
            $linkStmt->close();
        } else {
            $linkStmt = $conn->prepare("INSERT INTO nfc_cards (nfc_uid, student_uid, is_active) VALUES (?, ?, 1)");
            $linkStmt->bind_param("si", $nfcUid, $targetStudentUid);
            $linkStmt->execute();
            $linkStmt->close();
        }
        $checkNfc->close();

        $cardLinked = true;

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'NFC Card Assigned', ?, ?)");
        $details = "Linked NFC Card UID: $nfcUid to student UID: $targetStudentUid.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }

    if ($isNewStudent && !$cardLinked) {
        header("Location: dashboard.php?page=student_nfc_management&msg=saved_no_card");
    } else {
        header("Location: dashboard.php?page=student_nfc_management&msg=saved");
    }
    exit();
}

// Action: Direct NFC Link Execution
if (isset($_POST['link_card_direct'])) {
    $studentUid = (int)$_POST['student_uid'];
    $nfcUid = trim($_POST['nfc_uid'] ?? '');

    $check = $conn->prepare("SELECT card_id FROM nfc_cards WHERE nfc_uid = ?");
    $check->bind_param("s", $nfcUid);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        $card = $res->fetch_assoc();
        $cardId = $card['card_id'];
        $stmt = $conn->prepare("UPDATE nfc_cards SET student_uid = ?, is_active = 1 WHERE card_id = ?");
        $stmt->bind_param("ii", $studentUid, $cardId);
    } else {
        $stmt = $conn->prepare("INSERT INTO nfc_cards (nfc_uid, student_uid, is_active) VALUES (?, ?, 1)");
        $stmt->bind_param("si", $nfcUid, $studentUid);
    }

    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'NFC Card Assigned', ?, ?)");
        $details = "Directly linked NFC UID $nfcUid to student ID: $studentUid.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
    $stmt->close();
    $check->close();

    header("Location: dashboard.php?page=student_nfc_management&msg=linked");
    exit();
}

// Action: Disable NFC Card
if (isset($_POST['disable_card'])) {
    $cardId = (int)$_POST['card_id'];
    $stmt = $conn->prepare("UPDATE nfc_cards SET is_active = 0 WHERE card_id = ?");
    $stmt->bind_param("i", $cardId);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Card Disabled', ?, ?)");
        $details = "Disabled NFC card record ID: $cardId.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
    $stmt->close();
    header("Location: dashboard.php?page=student_nfc_management&view=disabled_cards&msg=disabled");
    exit();
}

// Action: Restore Disabled NFC Card
if (isset($_GET['restore_card_id'])) {
    $cardId = (int)$_GET['restore_card_id'];
    $stmt = $conn->prepare("UPDATE nfc_cards SET is_active = 1 WHERE card_id = ?");
    $stmt->bind_param("i", $cardId);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Card Restored', ?, ?)");
        $details = "Restored active status for NFC card ID: $cardId.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
    $stmt->close();
    header("Location: dashboard.php?page=student_nfc_management&view=disabled_cards&msg=restored");
    exit();
}

// Action: Archive Student Profile
if (isset($_POST['archive_student'])) {
    $id = (int)$_POST['student_uid'];
    $stmt = $conn->prepare("UPDATE students SET is_archived = 1 WHERE student_uid = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Student Archived', ?, ?)");
        $details = "Archived student record ID: $id.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
    $stmt->close();
    header("Location: dashboard.php?page=student_nfc_management&msg=archived");
    exit();
}

// Action: Restore Archived Student
if (isset($_GET['restore_student_id'])) {
    $id = (int)$_GET['restore_student_id'];
    $stmt = $conn->prepare("UPDATE students SET is_archived = 0 WHERE student_uid = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $auditStmt = $conn->prepare("INSERT INTO system_audit_logs (user_id, action, details, ip_address) VALUES (?, 'Student Restored', ?, ?)");
        $details = "Restored student record ID: $id from archive.";
        $auditStmt->bind_param("iss", $currentUserId, $details, $ipAddress);
        $auditStmt->execute();
        $auditStmt->close();
    }
    $stmt->close();
    header("Location: dashboard.php?page=student_nfc_management&view=archived&msg=restored");
    exit();
}

// -------------------------------------------------------------------------
// 2. QUERY CALCULATIONS AND DATA RETRIEVAL
// -------------------------------------------------------------------------

$currentView = $_GET['view'] ?? 'active'; // 'active', 'archived', 'disabled_cards'
$search      = trim($_GET['search'] ?? '');
$deptFilter  = $_GET['department'] ?? 'all';
$sortOrder   = strtoupper(trim($_GET['sort'] ?? 'ASC'));

$totalActiveStudents = $conn->query("SELECT COUNT(*) as c FROM students WHERE is_archived = 0 OR is_archived IS NULL")->fetch_assoc()['c'] ?? 0;
$totalArchived       = $conn->query("SELECT COUNT(*) as c FROM students WHERE is_archived = 1")->fetch_assoc()['c'] ?? 0;
$totalDisabledCards  = $conn->query("SELECT COUNT(*) as c FROM nfc_cards WHERE is_active = 0")->fetch_assoc()['c'] ?? 0;

$whereClauses = [];
$bindTypes    = "";
$bindParams   = [];

if ($currentView === 'archived') {
    $whereClauses[] = "s.is_archived = 1";
} elseif ($currentView === 'disabled_cards') {
    $whereClauses[] = "c.is_active = 0";
} else {
    $whereClauses[] = "(s.is_archived = 0 OR s.is_archived IS NULL)";
}

if (!empty($search)) {
    $whereClauses[] = "(s.student_id_no LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.course LIKE ? OR c.nfc_uid LIKE ?)";
    $bindTypes .= "sssss";
    $searchPattern = '%' . $search . '%';
    for ($i = 0; $i < 5; $i++) {
        $bindParams[] = $searchPattern;
    }
}

if ($deptFilter !== 'all' && $currentView !== 'disabled_cards') {
    $whereClauses[] = "s.department = ?";
    $bindTypes .= "s";
    $bindParams[] = $deptFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$sortDirection = ($sortOrder === 'DESC') ? 'DESC' : 'ASC';

if ($currentView === 'disabled_cards') {
    $mainQuery = "SELECT s.*, c.card_id, c.nfc_uid, c.is_active as card_is_active 
                  FROM nfc_cards c 
                  JOIN students s ON c.student_uid = s.student_uid 
                  $whereSql 
                  ORDER BY s.last_name $sortDirection";
} else {
    $mainQuery = "SELECT s.*, c.card_id, c.nfc_uid, c.is_active as card_is_active 
                  FROM students s 
                  LEFT JOIN nfc_cards c ON s.student_uid = c.student_uid AND c.is_active = 1
                  $whereSql 
                  ORDER BY s.last_name $sortDirection";
}

$stmtMain = $conn->prepare($mainQuery);
$result = null;

if ($stmtMain) {
    if (!empty($bindTypes)) {
        $stmtMain->bind_param($bindTypes, ...$bindParams);
    }
    $stmtMain->execute();
    $result = $stmtMain->get_result();
}
?>

<section class="admin-container">
    <header class="app-header header-flex">
        <div>
            <h2>Student & NFC Management</h2>
            <p>Unified administration for student registration, bulk CSV import, and NFC card assignment.</p>
        </div>
    </header>

    <div class="asymmetric-2col-grid">
        <!-- LEFT COLUMN: Integrated Form & Bulk Imports -->
        <div class="asymmetric-fixed-col flex-vertical-gap">
            <article class="form-card">
                <header class="app-header header-flex">
                    <h3 id="formCardTitle">Register / Edit Student</h3>
                </header>

                <form action="dashboard.php?page=student_nfc_management" method="POST" class="vertical-form" id="studentForm">
                    <input type="hidden" name="student_uid" id="student_uid" value="">

                    <div class="form-group-vertical">
                        <label for="student_id_no">Student ID Number</label>
                        <input type="text" id="student_id_no" name="student_id_no" class="filter-input" placeholder="2023-0366-M" required>
                    </div>

                    <div class="form-group-vertical">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="filter-input" placeholder="First Name" required>
                    </div>

                    <div class="form-group-vertical">
                        <label for="middlename">Middle Name</label>
                        <input type="text" id="middlename" name="middlename" class="filter-input" placeholder="Middle Name">
                    </div>

                    <div class="form-group-vertical">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="filter-input" placeholder="Last Name" required>
                    </div>

                    <div class="form-group-vertical">
                        <label for="department">Department</label>
                        <select id="department" name="department" class="filter-select" required onchange="handleDepartmentChange(this.value);">
                            <option value="">Select Department</option>
                            <option value="Computer Studies Department">Computer Studies Department</option>
                            <option value="Teacher Education Department">Teacher Education Department</option>
                            <option value="Industrial Technology Department">Industrial Technology Department</option>
                            <option value="Hospitality and Business Management Department">Hospitality and Business Management Department</option>
                        </select>
                    </div>

                    <div class="form-group-vertical">
                        <label for="course">Course</label>
                        <select id="course" name="course" class="filter-select" required disabled onchange="handleCourseChange(this.value);">
                            <option value="">Select Department First</option>
                        </select>
                    </div>

                    <div class="form-group-vertical">
                        <label for="major">Major / Specialization</label>
                        <select id="major" name="major" class="filter-select fixed-dropdown-scroll" disabled>
                            <option value="">None / N/A</option>
                        </select>
                    </div>

                    <div class="form-row-2col">
                        <div class="form-group-vertical">
                            <label for="year_level">Year</label>
                            <input type="number" id="year_level" name="year_level" class="filter-input" placeholder="1-4" min="1" max="4" required>
                        </div>
                        <div class="form-group-vertical">
                            <label for="section">Section</label>
                            <input type="text" id="section" name="section" class="filter-input" placeholder="A, B, C, ...">
                        </div>
                    </div>

                    <div class="form-group-vertical nfc-integrated-box">
                        <label for="nfc_scanner">Assign NFC Card (Tap or Type)</label>
                        <input type="text" name="nfc_uid" id="nfc_scanner" class="filter-input text-center font-mono" placeholder="Scan NFC tag now..." autocomplete="off">
                        <div id="scan_status_box" class="scan-status-container">
                            <span id="scan_status_text" class="status-pending">SCANNER READY</span>
                        </div>
                    </div>

                    <div class="form-actions-flex-row">
                        <button type="submit" name="save_student_record" id="formSubmitBtn" class="btn btn-primary full-width">
                            <img src="assets/icons/outline/device-floppy.svg" alt="Save Icon" class="asset-icon-img">
                            <span>Save Profile</span>
                        </button>
                        <button type="button" id="cancelEditBtn" class="btn btn-secondary hide-element" onclick="resetStudentForm()" title="Cancel Edit">
                            <img src="assets/icons/outline/x.svg" alt="Cancel Icon" class="asset-icon-img">
                            <span>Cancel Edit</span>
                        </button>
                    </div>
                </form>
            </article>

            <article class="form-card">
                <header class="app-header">
                    <h3>Bulk Import (CSV)</h3>
                </header>

                <form action="import_students.php" method="POST" enctype="multipart/form-data" class="vertical-form">
                    <div class="form-group-vertical">
                        <input type="file" name="student_csv" class="filter-input file-input-dashed" accept=".csv" required>
                    </div>
                    <div class="form-actions-vertical">
                        <button type="submit" name="import" class="btn btn-success full-width">
                           <img src="assets/icons/outline/upload.svg" alt="Upload Icon" class="asset-icon-img">
                            <span>Upload CSV</span>
                        </button>
                    </div>
                </form>
            </article>
        </div>

        <!-- RIGHT COLUMN: Chrome Segmented Tabs & Operational Master Table -->
        <article class="table-card asymmetric-fluid-col">
            <nav class="chrome-tabs-container">
                <a href="dashboard.php?page=student_nfc_management&view=active" class="chrome-tab <?php echo $currentView === 'active' ? 'active' : ''; ?>">
                    Active Registry (<?php echo $totalActiveStudents; ?>)
                </a>
                <a href="dashboard.php?page=student_nfc_management&view=archived" class="chrome-tab <?php echo $currentView === 'archived' ? 'active' : ''; ?>">
                    Archived (<?php echo $totalArchived; ?>)
                </a>
                <a href="dashboard.php?page=student_nfc_management&view=disabled_cards" class="chrome-tab <?php echo $currentView === 'disabled_cards' ? 'active' : ''; ?>">
                    Disabled Cards (<?php echo $totalDisabledCards; ?>)
                </a>
            </nav>

            <form method="GET" action="dashboard.php" class="filter-form-fixed">
                <input type="hidden" name="page" value="student_nfc_management">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($currentView, ENT_QUOTES, 'UTF-8', false); ?>">

                <div class="form-group flex-grow">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" class="filter-input" placeholder="Search Name, Student ID, Course, or NFC UID..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8', false); ?>">
                </div>

                <?php if ($currentView !== 'disabled_cards'): ?>
                <div class="form-group">
                    <label for="department_filter">Department:</label>
                    <select id="department_filter" name="department" class="filter-select">
                        <option value="all" <?php echo $deptFilter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="Computer Studies Department" <?php echo $deptFilter === 'Computer Studies Department' ? 'selected' : ''; ?>>Computer Studies</option>
                        <option value="Teacher Education Department" <?php echo $deptFilter === 'Teacher Education Department' ? 'selected' : ''; ?>>Teacher Education</option>
                        <option value="Industrial Technology Department" <?php echo $deptFilter === 'Industrial Technology Department' ? 'selected' : ''; ?>>Industrial Tech</option>
                        <option value="Hospitality and Business Management Department" <?php echo $deptFilter === 'Hospitality and Business Management Department' ? 'selected' : ''; ?>>HBM</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="sort">Sort Name:</label>
                    <select id="sort" name="sort" class="filter-select">
                        <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Name (A – Z)</option>
                        <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Name (Z – A)</option>
                    </select>
                </div>

                <div class="form-actions-inline">
                    <button type="submit" class="btn btn-primary">
                        <img src="assets/icons/outline/filter.svg" alt="Filter Icon" class="asset-icon-img">
                    </button>
                    <a href="dashboard.php?page=student_nfc_management&view=<?php echo htmlspecialchars($currentView, ENT_QUOTES, 'UTF-8', false); ?>" class="btn btn-secondary">
                        <img src="assets/icons/outline/refresh.svg" alt="Reset Icon" class="asset-icon-img">
                    </a>
                </div>
            </form>

            <div class="table-responsive table-scroll-600">
                <table class="data-table">
                    <thead class="table-header">
                        <tr>
                            <th>STUDENT NAME</th>
                            <th class="nowrap-cell">Course, Year & Section</th>
                            <th class="nowrap-cell">NFC CARD STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()):
                                $mi = !empty($row['middlename']) ? substr($row['middlename'], 0, 1) . "." : "";
                                $majorDisplay = !empty($row['major']) ? " ({$row['major']})" : "";
                                $hasNfc = !empty($row['nfc_uid']);
                        ?>
                        <tr class="clickable-row" onclick="toggleDetails('details-<?php echo $row['student_uid']; ?>', '<?php echo $row['student_uid']; ?>')">
                            <td>
                                <span class="student-name-lg"><?php echo htmlspecialchars("{$row['last_name']}, {$row['first_name']} $mi", ENT_QUOTES, 'UTF-8', false); ?></span>
                                <div class="student-id-subtext">
                                    <code class="code-box"><?php echo htmlspecialchars($row['student_id_no'], ENT_QUOTES, 'UTF-8', false); ?></code>
                                </div>
                            </td>
                            <td class="nowrap-cell">
                                <span class="font-semibold text-main">
                                    <?php echo htmlspecialchars("{$row['course']}{$majorDisplay} {$row['year_level']}-{$row['section']}", ENT_QUOTES, 'UTF-8', false); ?>
                                </span>
                                <div class="department-subtext">
                                    <small class="text-muted-sm"><?php echo htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8', false); ?></small>
                                </div>
                            </td>
                            <td class="nowrap-cell">
                                <?php if ($hasNfc && $currentView !== 'disabled_cards'): ?>
                                    <span class="nfc-tag-badge active-tag">
                                        <img src="assets/icons/outline/link.svg" alt="Link Icon" class="asset-icon-img">
                                        <code><?php echo htmlspecialchars($row['nfc_uid'], ENT_QUOTES, 'UTF-8', false); ?></code>
                                    </span>
                                <?php elseif ($currentView === 'disabled_cards'): ?>
                                    <span class="nfc-tag-badge disabled-tag">
                                        <code><?php echo htmlspecialchars($row['nfc_uid'], ENT_QUOTES, 'UTF-8', false); ?></code>
                                    </span>
                                <?php else: ?>
                                    <span class="nfc-tag-badge unlinked-tag">UNLINKED</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Expandable Quick-Action Drawer Row -->
                        <tr id="details-<?php echo $row['student_uid']; ?>" class="expandable-detail-row hide-element">
                            <td colspan="3">
                                <div class="expanded-card-wrapper">
                                    <div class="expanded-info-grid">
                                        <p><strong>Record UID:</strong> #<?php echo $row['student_uid']; ?></p>
                                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars("{$row['first_name']} {$row['middlename']} {$row['last_name']}", ENT_QUOTES, 'UTF-8', false); ?></p>
                                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($row['student_id_no'], ENT_QUOTES, 'UTF-8', false); ?></p>
                                        <p><strong>Department:</strong> <?php echo htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8', false); ?></p>
                                        <p><strong>Course & Major:</strong> <?php echo htmlspecialchars($row['course'] . $majorDisplay, ENT_QUOTES, 'UTF-8', false); ?></p>
                                        <p><strong>Year & Section:</strong> <?php echo htmlspecialchars("{$row['year_level']}-{$row['section']}", ENT_QUOTES, 'UTF-8', false); ?></p>
                                        <p><strong>NFC Hardware Status:</strong> <?php echo $hasNfc ? 'Linked (' . htmlspecialchars($row['nfc_uid'], ENT_QUOTES, 'UTF-8', false) . ')' : 'No active tag assigned'; ?></p>
                                    </div>
                                    
                                    <div class="expanded-action-bar">
                                        <?php if ($currentView === 'archived'): ?>
                                            <button type="button" class="btn btn-primary" title="Restore Student Profile" onclick="showGlobalModal('Restore Student Profile', 'Are you sure you want to restore this student profile?', function() { window.location.href = 'dashboard.php?page=student_nfc_management&view=archived&restore_student_id=<?php echo $row['student_uid']; ?>'; })">
                                                <img src="assets/icons/outline/restore.svg" alt="Restore Icon" class="asset-icon-img">
                                                <span>Restore Profile</span>
                                            </button>
                                        <?php elseif ($currentView === 'disabled_cards'): ?>
                                            <button type="button" class="btn btn-primary" title="Reactivate NFC Card" onclick="showGlobalModal('Reactivate NFC Card', 'Are you sure you want to reactivate this NFC card?', function() { window.location.href = 'dashboard.php?page=student_nfc_management&view=disabled_cards&restore_card_id=<?php echo $row['card_id']; ?>'; })">
                                                <img src="assets/icons/outline/link.svg" alt="Link Icon" class="asset-icon-img">
                                                <span>Reactivate Card</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-icon-only" title="Edit Profile" onclick="loadStudentIntoForm({
                                                student_uid: '<?php echo $row['student_uid']; ?>',
                                                student_id_no: '<?php echo htmlspecialchars($row['student_id_no'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                first_name: '<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                middlename: '<?php echo htmlspecialchars($row['middlename'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                last_name: '<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                department: '<?php echo htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                course: '<?php echo htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                major: '<?php echo htmlspecialchars($row['major'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                year_level: '<?php echo htmlspecialchars($row['year_level'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                section: '<?php echo htmlspecialchars($row['section'], ENT_QUOTES, 'UTF-8', false); ?>',
                                                nfc_uid: '<?php echo htmlspecialchars($row['nfc_uid'] ?? '', ENT_QUOTES, 'UTF-8', false); ?>'
                                            })">
                                                <img src="assets/icons/outline/edit.svg" alt="Edit Icon" class="asset-icon-img">
                                            </button>

                                            <?php if ($hasNfc): ?>
                                                <form method="POST" class="inline-form" id="disableForm_<?php echo $row['card_id']; ?>">
                                                    <input type="hidden" name="card_id" value="<?php echo $row['card_id']; ?>">
                                                    <input type="hidden" name="disable_card" value="1">
                                                    <button type="button" class="btn btn-secondary btn-icon-only" title="Unlink Card" onclick="showGlobalModal('Unlink NFC Card', 'Are you sure you want to disable/unlink the NFC card for <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8', false); ?>?', function() { document.getElementById('disableForm_<?php echo $row['card_id']; ?>').submit(); }, 'Unlink Card', true)">
                                                        <img src="assets/icons/outline/link-off.svg" alt="Disable Icon" class="asset-icon-img">
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="POST" class="inline-form" id="archiveForm_<?php echo $row['student_uid']; ?>">
                                                <input type="hidden" name="student_uid" value="<?php echo $row['student_uid']; ?>">
                                                <input type="hidden" name="archive_student" value="1">
                                                <button type="button" class="btn btn-warning-archive btn-icon-only" title="Archive Profile" onclick="showGlobalModal('Archive Student Profile', 'Are you sure you want to move <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8', false); ?> to archives?', function() { document.getElementById('archiveForm_<?php echo $row['student_uid']; ?>').submit(); }, 'Archive Record', true)">
                                                    <img src="assets/icons/outline/archive.svg" alt="Archive Icon" class="asset-icon-img">
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center pad-20">No student or hardware records match your current view/filter criteria.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>

<script>
const academicHierarchy = {
    "Computer Studies Department": {
        "BSIT": { name: "Bachelor of Science in Information Technology", majors: [] },
        "BSIS": { name: "Bachelor of Science in Information Systems", majors: [] }
    },
    "Teacher Education Department": {
        "BEED": { name: "Bachelor of Elementary Education", majors: [] },
        "BSED": { name: "Bachelor of Secondary Education", majors: ["English", "Filipino", "Mathematics", "Science", "Social Studies"] },
        "BTVTED": { name: "Bachelor of Technical-Vocational Teacher Education", majors: ["Food Service Management"] },
        "BTLED": { name: "Bachelor of Technology and Livelihood Education", majors: ["Home Economics"] }
    },
    "Hospitality and Business Management Department": {
        "BSHM": { name: "Bachelor of Science in Hospitality Management", majors: [] },
        "BSTM": { name: "Bachelor of Science in Tourism Management", majors: [] },
        "BS Entrep": { name: "Bachelor of Science in Entrepreneurship", majors: [] }
    },
    "Industrial Technology Department": {
        "BIndTech": { 
            name: "Bachelor of Industrial Technology", 
            majors: [
                "Apparel and Fashion Technology", "Architectural Drafting Technology", "Automotive Technology",
                "Beauty Care and Wellness Technology", "Culinary Technology", "Electrical Technology",
                "Electronics Technology", "Welding and Fabrication Technology", "Heating Ventilating Air Conditioning-Refrigeration Technology"
            ] 
        }
    }
};

let activeDepartment = '';

function handleDepartmentChange(selectedDept, targetCourse = '', targetMajor = '') {
    activeDepartment = selectedDept;
    const courseSelect = document.getElementById('course');
    const majorSelect = document.getElementById('major');

    courseSelect.innerHTML = '<option value="">Select Course</option>';
    majorSelect.innerHTML = '<option value="">None / N/A</option>';
    majorSelect.disabled = true;

    if (selectedDept && academicHierarchy[selectedDept]) {
        courseSelect.disabled = false;
        Object.keys(academicHierarchy[selectedDept]).forEach(code => {
            const courseObj = academicHierarchy[selectedDept][code];
            const opt = document.createElement('option');
            opt.value = code;
            opt.textContent = `${code} - ${courseObj.name}`;
            if (code === targetCourse) opt.selected = true;
            courseSelect.appendChild(opt);
        });
        if (targetCourse) handleCourseChange(targetCourse, targetMajor);
    } else {
        courseSelect.disabled = true;
        courseSelect.innerHTML = '<option value="">Select Department First</option>';
    }
}

function handleCourseChange(selectedCourse, targetMajor = '') {
    const majorSelect = document.getElementById('major');
    majorSelect.innerHTML = '<option value="">None / N/A</option>';

    if (activeDepartment && selectedCourse && academicHierarchy[activeDepartment][selectedCourse]) {
        const majors = academicHierarchy[activeDepartment][selectedCourse].majors;
        if (majors && majors.length > 0) {
            majorSelect.disabled = false;
            majors.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m;
                if (m === targetMajor) opt.selected = true;
                majorSelect.appendChild(opt);
            });
        } else {
            majorSelect.disabled = true;
        }
    } else {
        majorSelect.disabled = true;
    }
}

function toggleDetails(rowId, studentUid) {
    const detailRow = document.getElementById(rowId);
    if (detailRow) {
        const isExpanding = detailRow.classList.contains('hide-element');
        if (isExpanding) {
            detailRow.classList.remove('hide-element');
            if (studentUid) {
                fetch(`dashboard.php?page=student_nfc_management&log_view=1&student_uid=${studentUid}`, { method: 'GET' })
                    .catch(err => console.error('Audit log error:', err));
            }
        } else {
            detailRow.classList.add('hide-element');
        }
    }
}

function loadStudentIntoForm(student) {
    document.getElementById('student_uid').value = student.student_uid;
    document.getElementById('student_id_no').value = student.student_id_no || student.school_id_no || '';
    document.getElementById('first_name').value = student.first_name;
    document.getElementById('middlename').value = student.middlename;
    document.getElementById('last_name').value = student.last_name;
    document.getElementById('department').value = student.department;
    handleDepartmentChange(student.department, student.course, student.major);

    document.getElementById('year_level').value = student.year_level;
    document.getElementById('section').value = student.section;
    document.getElementById('nfc_scanner').value = student.nfc_uid || '';

    document.getElementById('formCardTitle').innerText = 'Edit Student Profile';
    document.getElementById('formSubmitBtn').querySelector('span').innerText = 'Update Profile';
    document.getElementById('cancelEditBtn').classList.remove('hide-element');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetStudentForm() {
    document.getElementById('studentForm').reset();
    document.getElementById('student_uid').value = '';
    document.getElementById('course').innerHTML = '<option value="">Select Department First</option>';
    document.getElementById('course').disabled = true;
    document.getElementById('major').innerHTML = '<option value="">None / N/A</option>';
    document.getElementById('major').disabled = true;
    document.getElementById('formCardTitle').innerText = 'Register / Edit Student';
    document.getElementById('formSubmitBtn').querySelector('span').innerText = 'Save Profile';
    document.getElementById('cancelEditBtn').classList.add('hide-element');
}

function decimalToLittleEndianHex(decimalString) {
    let num = BigInt(decimalString);
    let hex = num.toString(16).toLowerCase();
    if (hex.length % 2 !== 0) hex = "0" + hex;
    let bytes = hex.match(/.{1,2}/g);
    bytes.reverse(); 
    return bytes.join(':');
}

function checkCardAvailability(uid) {
    const statusText = document.getElementById('scan_status_text');
    fetch('check_card.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'nfc_uid=' + encodeURIComponent(uid)
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            statusText.className = "status-unsettled";
            statusText.innerText = "TAG ALREADY LINKED TO: " + data.student_name;
            if (typeof showToast === 'function') {
                showToast('Card Duplicate Error', "This card is already linked to " + data.student_name + ". Input cleared.", 'error');
            }
            document.getElementById('nfc_scanner').value = '';
        } else {
            statusText.className = "status-settled";
            statusText.innerText = "CARD READY TO LINK";
            if (typeof showToast === 'function') {
                showToast('Card Validated', 'NFC Tag is unassigned and ready to link.', 'success');
            }
        }
    }).catch(() => {
        statusText.className = "status-pending";
        statusText.innerText = "SCANNER READY";
    });
}

const nfcScanner = document.getElementById('nfc_scanner');
if (nfcScanner) {
    nfcScanner.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            if (this.value.length > 0) {
                let val = this.value;
                if (/^\d+$/.test(val)) {
                    val = decimalToLittleEndianHex(val);
                    this.value = val;
                }
                checkCardAvailability(val);
            }
        }
    });

    if ('NDEFReader' in window) {
        window.addEventListener('load', async () => {
            try {
                const ndef = new NDEFReader();
                await ndef.scan();
                ndef.onreading = ({ serialNumber }) => {
                    let bytes = serialNumber.includes(':') ? serialNumber.split(':') : serialNumber.match(/.{1,2}/g);
                    bytes.reverse();
                    let finalUid = bytes.join(':');
                    nfcScanner.value = finalUid;
                    checkCardAvailability(finalUid);
                };
            } catch (err) {
                console.log("WebNFC Inactive or Not Supported.");
            }
        });
    }
}
</script>

<?php if (isset($_GET['msg'])): ?>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof showToast === 'function') {
        <?php if ($_GET['msg'] === 'saved'): ?>
            showToast('Record Saved', 'Student profile has been successfully saved.', 'success');
        <?php elseif ($_GET['msg'] === 'saved_no_card'): ?>
            showToast('Attention: Unlinked Student', 'New student profile created successfully, but no NFC Student ID card was linked.', 'warning');
        <?php elseif ($_GET['msg'] === 'linked'): ?>
            showToast('NFC Card Assigned', 'NFC tag was successfully linked to the student.', 'success');
        <?php elseif ($_GET['msg'] === 'disabled'): ?>
            showToast('NFC Card Disabled', 'The NFC card has been disabled.', 'warning');
        <?php elseif ($_GET['msg'] === 'restored'): ?>
            showToast('Record Restored', 'The record was successfully restored.', 'success');
        <?php elseif ($_GET['msg'] === 'archived'): ?>
            showToast('Student Archived', 'Student profile moved to archive.', 'warning');
        <?php endif; ?>
    }
});
</script>
<?php endif; ?>