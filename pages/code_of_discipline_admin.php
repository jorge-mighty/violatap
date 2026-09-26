<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    echo "<article class='table-card'><h2>Access Restricted</h2><p>Please log in to continue.</p></article>";
    exit();
}

$currentRole   = trim($_SESSION['role'] ?? '');
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$normRole      = normalize_role_name($currentRole);

// Verify module access permissions using central helper
$hasAccess = has_module_access($currentRole, 'code_of_discipline', $conn);
if (!$hasAccess && $normRole !== 'superadmin' && $normRole !== 'csoadmin' && $normRole !== 'cso') {
    echo "<article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to view the Code of Discipline.</p></article>";
    exit();
}

// Committee on Discipline (COD) and Superadmin hold full write privileges. CSO has read-only access.
$canEditCod = ($normRole === 'superadmin' || $normRole === 'codadmin' || $normRole === 'committeeondiscipline' || $normRole === 'cod');

$editData = null;

// Action 1: Add or Edit Offense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    if (!$canEditCod) {
        echo "<article class='table-card'><h2>Action Restricted</h2><p>You do not have permission to modify offenses.</p></article>";
        exit();
    }

    $offense     = trim($_POST['offense'] ?? '');
    $category    = trim($_POST['category'] ?? 'Minor');
    $description = trim($_POST['description'] ?? '');
    $sanction1st = trim($_POST['sanction_1st'] ?? '');
    $sanction2nd = trim($_POST['sanction_2nd'] ?? '');
    $sanction3rd = trim($_POST['sanction_3rd'] ?? '');
    $actionType  = $_POST['action_type'];

    if ($actionType === 'add') {
        $stmt = $conn->prepare("INSERT INTO code_of_discipline (offense, category, description, sanction_1st, sanction_2nd, sanction_3rd, is_archived) VALUES (?, ?, ?, ?, ?, ?, 0)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $offense, $category, $description, $sanction1st, $sanction2nd, $sanction3rd);
            if ($stmt->execute()) {
                if (function_exists('log_activity')) {
                    log_activity($conn, $currentUserId, 'Offense Added', "Added new offense: {$offense} ({$category})");
                }
                $_SESSION['flash_message'] = "New offense successfully created and added to the Code of Discipline.";
            }
            $stmt->close();
            header("Location: dashboard.php?page=code_of_discipline_admin");
            exit();
        }
    } elseif ($actionType === 'edit') {
        $offenseId = (int)($_POST['offense_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE code_of_discipline SET offense = ?, category = ?, description = ?, sanction_1st = ?, sanction_2nd = ?, sanction_3rd = ? WHERE offense_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssi", $offense, $category, $description, $sanction1st, $sanction2nd, $sanction3rd, $offenseId);
            if ($stmt->execute()) {
                if (function_exists('log_activity')) {
                    log_activity($conn, $currentUserId, 'Offense Updated', "Updated offense ID #{$offenseId}: {$offense}");
                }
                $_SESSION['flash_message'] = "Offense details and sanctions successfully updated.";
            }
            $stmt->close();
            header("Location: dashboard.php?page=code_of_discipline_admin");
            exit();
        }
    }
}

// Action 2: Archive Offense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_offense'])) {
    if (!$canEditCod) {
        echo "<article class='table-card'><h2>Action Restricted</h2><p>You do not have permission to archive offenses.</p></article>";
        exit();
    }

    $offenseId = (int)($_POST['offense_id'] ?? 0);
    $stmt = $conn->prepare("UPDATE code_of_discipline SET is_archived = 1 WHERE offense_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $offenseId);
        if ($stmt->execute()) {
            if (function_exists('log_activity')) {
                log_activity($conn, $currentUserId, 'Offense Archived', "Archived offense record ID #{$offenseId}");
            }
            $_SESSION['flash_message'] = "Offense record successfully moved to archive.";
        }
        $stmt->close();
        header("Location: dashboard.php?page=code_of_discipline_admin");
        exit();
    }
}

// Action 3: Restore Offense from Archive
if (isset($_GET['toggle_archive']) && (int)($_GET['status'] ?? -1) === 0) {
    if (!$canEditCod) {
        echo "<article class='table-card'><h2>Action Restricted</h2><p>You do not have permission to restore offenses.</p></article>";
        exit();
    }

    $offenseId = (int)$_GET['toggle_archive'];
    $stmt = $conn->prepare("UPDATE code_of_discipline SET is_archived = 0 WHERE offense_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $offenseId);
        if ($stmt->execute()) {
            if (function_exists('log_activity')) {
                log_activity($conn, $currentUserId, 'Offense Restored', "Restored offense record ID #{$offenseId} from archive");
            }
            $_SESSION['flash_message'] = "Offense record successfully restored from archive.";
        }
        $stmt->close();
        header("Location: dashboard.php?page=code_of_discipline_admin&view=archived");
        exit();
    }
}

$viewArchived = (isset($_GET['view']) && $_GET['view'] === 'archived') ? 1 : 0;

$stmtRecords = $conn->prepare("SELECT * FROM code_of_discipline WHERE is_archived = ? ORDER BY category ASC, offense_id ASC");
$records = null;
if ($stmtRecords) {
    $stmtRecords->bind_param("i", $viewArchived);
    $stmtRecords->execute();
    $records = $stmtRecords->get_result();
}
?>

<section class="admin-container">
    <header class="app-header header-flex-aligned gap-margin-bottom">
        <div>
            <h2>Code of Discipline Management</h2>
            <p>Configure student offenses, descriptions, and progressive sanctions.</p>
        </div>
        <nav class="nav-align-right header-actions-inline">
            <?php if ($viewArchived): ?>
                <a href="dashboard.php?page=code_of_discipline_admin" class="btn btn-primary">
                    <img src="assets/icons/outline/book-2.svg" alt="Active Icon" class="asset-icon-img">
                    <span>Table of Offenses</span>
                </a>
            <?php else: ?>
                <?php if ($canEditCod): ?>
                    <button type="button" class="btn btn-primary" onclick="openAddCodModal()">
                        <img src="assets/icons/outline/plus.svg" alt="Add Icon" class="asset-icon-img">
                        <span title="Add New Offense">Add</span>
                    </button>
                <?php endif; ?>
                <a href="dashboard.php?page=code_of_discipline_admin&view=archived" class="btn btn-secondary">
                    <img src="assets/icons/outline/archive.svg" alt="Archive Icon" class="asset-icon-img">
                    <span title="View Archived Offenses">Archive</span>
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <article class="table-card">
        <header class="app-header header-flex-aligned">
            <h3><?php echo $viewArchived ? 'Archived Offenses' : 'Section 2. Table of Offenses and Remedial Action'; ?></h3>
            <div class="filter-form-fixed filter-form-aligned">
                <div class="form-group form-group-no-margin">
                    <input type="text" id="tableSearch" class="filter-input" placeholder="Search offenses...">
                </div>
                <div class="form-group form-group-no-margin">
                    <select id="tableCategoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        <option value="Minor">Minor</option>
                        <option value="Major">Major</option>
                    </select>
                </div>
            </div>
        </header>
        
        <div class="table-responsive">
            <table class="data-table cod-table-custom" id="codTable">
                <thead class="table-header">
                    <tr>
                        <th class="col-cod-offense">Offense</th>
                        <th class="col-cod-category">Category</th>
                        <th class="col-cod-description">Description</th>
                        <th class="col-cod-sanction">1st Sanction</th>
                        <th class="col-cod-sanction">2nd Sanction</th>
                        <th class="col-cod-sanction">3rd Sanction</th>
                        <th class="col-cod-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records && $records->num_rows > 0): ?>
                        <?php while ($row = $records->fetch_assoc()): ?>
                            <tr data-category="<?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="col-cod-offense"><strong><?php echo htmlspecialchars($row['offense'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td class="col-cod-category">
                                    <span class="<?php echo ($row['category'] === 'Major') ? 'status-unsettled' : 'status-pending'; ?>">
                                        <?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="col-cod-description"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="col-cod-sanction"><?php echo nl2br(htmlspecialchars($row['sanction_1st'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td class="col-cod-sanction"><?php echo nl2br(htmlspecialchars($row['sanction_2nd'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td class="col-cod-sanction"><?php echo nl2br(htmlspecialchars($row['sanction_3rd'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td class="col-cod-actions">
                                    <?php if ($canEditCod): ?>
                                        <?php if ($viewArchived): ?>
                                            <button type="button" class="btn btn-primary btn-icon-only" title="Restore Offense" onclick="showGlobalModal('Restore Offense', 'Are you sure you want to restore this offense?', function() { window.location.href = 'dashboard.php?page=code_of_discipline_admin&view=archived&toggle_archive=<?php echo $row['offense_id']; ?>&status=0'; })">
                                                <img src="assets/icons/outline/rotate-clockwise.svg" alt="Restore Icon" class="asset-icon-img">
                                            </button>
                                        <?php else: ?>
                                            <div class="cod-action-group">
                                                <button type="button" class="btn btn-secondary btn-icon-only" title="Edit Offense" onclick='openEditCodModal(<?php echo json_encode([
                                                    'offense_id'   => $row['offense_id'],
                                                    'offense'      => $row['offense'],
                                                    'category'     => $row['category'],
                                                    'description'  => $row['description'],
                                                    'sanction_1st' => $row['sanction_1st'],
                                                    'sanction_2nd' => $row['sanction_2nd'],
                                                    'sanction_3rd' => $row['sanction_3rd']
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <img src="assets/icons/outline/edit.svg" alt="Edit Icon" class="asset-icon-img">
                                                </button> 
                                                <form method="POST" class="inline-form" id="archive_form_<?php echo $row['offense_id']; ?>">
                                                    <input type="hidden" name="offense_id" value="<?php echo $row['offense_id']; ?>">
                                                    <input type="hidden" name="archive_offense" value="1">
                                                    <button type="button" class="btn btn-warning-archive btn-icon-only" title="Archive Offense" onclick="showGlobalModal('Archive Offense', 'Are you sure you want to archive <?php echo htmlspecialchars($row['offense'], ENT_QUOTES, 'UTF-8'); ?>?', function() { document.getElementById('archive_form_<?php echo $row['offense_id']; ?>').submit(); }, 'Archive', true)">
                                                        <img src="assets/icons/outline/archive.svg" alt="Archive Icon" class="asset-icon-img">
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-deactive btn-icon-only" disabled title="Read-only access">
                                            <img src="assets/icons/outline/lock.svg" alt="Locked Icon" class="asset-icon-img">
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No <?php echo $viewArchived ? 'archived' : 'active'; ?> offenses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<!-- Floating Window Modal: Add / Edit Offense Form -->
<?php if ($canEditCod): ?>
<div id="codModalOverlay" class="modal-backdrop-overlay">
    <div class="modal-card-wrapper modal-card-lg">
        <header class="modal-card-header">
            <h3 id="codModalTitle">Add New Offense</h3>
            <button type="button" class="modal-close-btn" onclick="closeCodModal()">&times;</button>
        </header>

        <form method="POST" action="dashboard.php?page=code_of_discipline_admin" class="vertical-form" id="codModalForm">
            <input type="hidden" name="action_type" id="cod_action_type" value="add">
            <input type="hidden" name="offense_id" id="cod_offense_id" value="">

            <div class="form-group-vertical">
                <label for="offense">Short Title (Offense)</label>
                <input type="text" id="offense" name="offense" class="filter-input" placeholder="e.g. ID Violations" required>
            </div>

            <div class="form-group-vertical">
                <label for="category">Category</label>
                <select id="category" name="category" class="filter-select" required>
                    <option value="Minor">Minor</option>
                    <option value="Major">Major</option>
                </select>
            </div>

            <div class="form-group-vertical">
                <label for="description">Full Description</label>
                <textarea id="description" name="description" class="filter-input" rows="2" placeholder="Full details of the offense..." required></textarea>
            </div>

            <div class="form-group-vertical">
                <label for="sanction_1st">1st Offense Sanction</label>
                <textarea id="sanction_1st" name="sanction_1st" class="filter-input" rows="2" placeholder="Sanctions for 1st offense..." required></textarea>
            </div>

            <div class="form-group-vertical">
                <label for="sanction_2nd">2nd Offense Sanction</label>
                <textarea id="sanction_2nd" name="sanction_2nd" class="filter-input" rows="2" placeholder="Sanctions for 2nd offense..." required></textarea>
            </div>

            <div class="form-group-vertical">
                <label for="sanction_3rd">3rd Offense Sanction</label>
                <textarea id="sanction_3rd" name="sanction_3rd" class="filter-input" rows="2" placeholder="Sanctions for 3rd offense..." required></textarea>
            </div>

            <div class="modal-actions-row">
                <button type="button" class="btn btn-secondary" onclick="closeCodModal()">
                    <img src="assets/icons/outline/x.svg" alt="Cancel Icon" class="asset-icon-img">
                    <span>Cancel</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <img src="assets/icons/outline/device-floppy.svg" alt="Save Icon" class="asset-icon-img">
                    <span id="codSubmitBtnText">Add Offense</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    const categoryFilter = document.getElementById('tableCategoryFilter');
    const tableRows = document.querySelectorAll('#codTable tbody tr');

    function filterTable() {
        const query = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;

        tableRows.forEach(row => {
            if (row.cells.length <= 1) return;
            const textContent = row.textContent.toLowerCase();
            const rowCategory = row.getAttribute('data-category');

            const matchesSearch = textContent.includes(query);
            const matchesCategory = !selectedCategory || rowCategory === selectedCategory;

            row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('keyup', filterTable);
    if (categoryFilter) categoryFilter.addEventListener('change', filterTable);
});

function openAddCodModal() {
    const modal = document.getElementById('codModalOverlay');
    if (!modal) return;
    document.getElementById('codModalForm').reset();
    document.getElementById('cod_action_type').value = 'add';
    document.getElementById('cod_offense_id').value = '';
    document.getElementById('codModalTitle').textContent = 'Add New Offense';
    document.getElementById('codSubmitBtnText').textContent = 'Add Offense';
    modal.classList.add('active');
}

function openEditCodModal(data) {
    const modal = document.getElementById('codModalOverlay');
    if (!modal) return;
    document.getElementById('cod_action_type').value = 'edit';
    document.getElementById('cod_offense_id').value = data.offense_id;
    document.getElementById('offense').value = data.offense;
    document.getElementById('category').value = data.category;
    document.getElementById('description').value = data.description;
    document.getElementById('sanction_1st').value = data.sanction_1st;
    document.getElementById('sanction_2nd').value = data.sanction_2nd;
    document.getElementById('sanction_3rd').value = data.sanction_3rd;
    document.getElementById('codModalTitle').textContent = 'Edit Offense #' + data.offense_id;
    document.getElementById('codSubmitBtnText').textContent = 'Update Offense';
    modal.classList.add('active');
}

function closeCodModal() {
    const modal = document.getElementById('codModalOverlay');
    if (modal) modal.classList.remove('active');
}
</script>