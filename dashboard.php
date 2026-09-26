<?php 
ob_start();
define('ALLOWED_ACCESS', true);
require_once 'db_config.php';
require_once 'audit_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$role = trim($_SESSION['role']);

// Guard Restriction: Deny web access, force mobile app use
if (strcasecmp($role, 'Guard') === 0) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ViolaTap | Access Denied</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="icon" type="image/png" href="assets/images/violatap.png">
    </head>
    <body class="access-denied-wrapper">
        <article class="access-denied-card">
            <img src="assets/images/violatap.png" alt="ViolaTap Logo" class="brand-logo-large">
            <h1>Access Denied</h1>
            <p>Web dashboard access is restricted for your account type. Guard accounts are only authorized to use the ViolaTap mobile application.</p>
            <a href="logout.php" class="btn btn-primary">Sign Out</a>
        </article>
    </body>
    </html>
    <?php
    exit();
}

// Strict Whitelist Mapping to eliminate Local File Inclusion (LFI) vulnerabilities
$allowedPagesMap = [
    'analytics'                  => 'pages/analytics.php',
    'violation_records'          => 'pages/violation_records.php',
    'code_of_discipline_admin'   => 'pages/code_of_discipline_admin.php',
    'student_nfc_management'     => 'pages/student_nfc_management.php',
    'user_management'            => 'pages/user_management.php',
    'settings'                   => 'pages/settings.php',
    'audit_logs'                 => 'pages/audit_logs.php'
];

$pageToModuleMap = [
    'analytics'                  => 'dashboard',
    'violation_records'          => 'violation_records',
    'code_of_discipline_admin'   => 'code_of_discipline',
    'student_nfc_management'     => 'student_nfc_management',
    'user_management'            => 'user_management',
    'settings'                   => 'settings',
    'audit_logs'                 => 'audit_logs'
];

$requestedPage = $_GET['page'] ?? 'analytics';

// Validate requested page against strict key whitelist
if (!array_key_exists($requestedPage, $allowedPagesMap)) {
    $requestedPage = 'analytics';
}

$targetModuleKey = $pageToModuleMap[$requestedPage];

// Check dynamic access permission via centralized RBAC query helper
$isPageAllowed = has_module_access($role, $targetModuleKey, $conn);

// If requested page is restricted for role, find first authorized view
if (!$isPageAllowed && normalize_role_name($role) !== 'superadmin') {
    $fallbackFound = false;
    foreach ($pageToModuleMap as $pKey => $mKey) {
        if (has_module_access($role, $mKey, $conn)) {
            $requestedPage = $pKey;
            $targetModuleKey = $mKey;
            $isPageAllowed = true;
            $fallbackFound = true;
            break;
        }
    }
    if (!$fallbackFound) {
        $requestedPage = 'analytics';
    }
}

$currentUserId = $_SESSION['user_id'] ?? 0;
$displayLastName = !empty($_SESSION['last_name']) ? $_SESSION['last_name'] : (!empty($_SESSION['username']) ? $_SESSION['username'] : 'SysAdmin');

// Verify Profile Picture Path
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViolaTap | <?php echo ucwords(str_replace('_', ' ', $requestedPage)); ?></title>
    <script src="assets/js/chart.umd.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="assets/images/violatap.png">
    <link rel="apple-touch-icon" href="assets/images/violatap.png">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <aside id="mainSidebar">
        <header class="auth-form-header">
            <a href="dashboard.php?page=analytics" class="brand-link-wrapper" title="Go to Home Dashboard">
                <img src="assets/images/violatap.png" alt="ViolaTap Logo" class="form-header-logo">
                <h3>ViolaTap</h3>
            </a>
        </header>
        
        <nav>
            <ul>
                <?php if (has_module_access($role, 'dashboard', $conn)): ?>
                    <li>
                        <a href="dashboard.php?page=analytics" class="<?php echo $requestedPage === 'analytics' ? 'active' : ''; ?>">
                            <img src="assets/icons/outline/layout-dashboard.svg" alt="Dashboard" class="asset-icon-img">
                            <span title="Navigate to Analytics Dashboard">Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (has_module_access($role, 'violation_records', $conn)): ?>
                    <li>
                        <a href="dashboard.php?page=violation_records" class="<?php echo $requestedPage === 'violation_records' ? 'active' : ''; ?>">
                            <img src="assets/icons/outline/file-text.svg" alt="Violation Records" class="asset-icon-img">
                            <span title="Navigate to Violation Records">Violation Records</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <section>
                <h4>Administration</h4>
                <ul>
                    <?php if (has_module_access($role, 'code_of_discipline', $conn)): ?>
                        <li>
                            <a href="dashboard.php?page=code_of_discipline_admin" class="<?php echo $requestedPage === 'code_of_discipline_admin' ? 'active' : ''; ?>">
                                <img src="assets/icons/outline/book-2.svg" alt="Code of Discipline" class="asset-icon-img">
                                <span title="Navigate to Code of Discipline">Code of Discipline</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (has_module_access($role, 'student_nfc_management', $conn)): ?>
                        <li>
                            <a href="dashboard.php?page=student_nfc_management" class="<?php echo $requestedPage === 'student_nfc_management' ? 'active' : ''; ?>">
                                <img src="assets/icons/outline/users.svg" alt="Student Registry" class="asset-icon-img">
                                <span title="Navigate to Student Registry">Student Registry</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (has_module_access($role, 'user_management', $conn)): ?>
                        <li>
                            <a href="dashboard.php?page=user_management" class="<?php echo $requestedPage === 'user_management' ? 'active' : ''; ?>">
                                <img src="assets/icons/outline/user.svg" alt="User Management" class="asset-icon-img">
                                <span title="Navigate to User Management">User Management</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if (has_module_access($role, 'settings', $conn) || has_module_access($role, 'audit_logs', $conn)): ?>
                    <h4>Admin Settings</h4>
                    <ul>
                        <?php if (has_module_access($role, 'audit_logs', $conn)): ?>
                            <li>
                                <a href="dashboard.php?page=audit_logs" class="<?php echo $requestedPage === 'audit_logs' ? 'active' : ''; ?>">
                                    <img src="assets/icons/outline/clock-hour-5.svg" alt="Audit Logs" class="asset-icon-img">
                                    <span title="Navigate to Audit Logs">Audit Logs</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (has_module_access($role, 'settings', $conn)): ?>
                            <li>
                                <a href="dashboard.php?page=settings" class="<?php echo $requestedPage === 'settings' ? 'active' : ''; ?>">
                                    <img src="assets/icons/outline/settings.svg" alt="Settings" class="asset-icon-img">
                                    <span title="Navigate to Settings">Settings</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </nav>

        <footer>
            <a href="logout.php">
                <img src="assets/icons/outline/logout.svg" alt="Sign Out" class="asset-icon-img">
                <span>Sign Out</span>
            </a>
        </footer>
    </aside>

    <main>
        <header class="app-top-bar">
            <div class="brand-group">
                <button type="button" id="burgerBtn" class="burger-menu-btn" aria-label="Toggle Navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="dashboard.php?page=analytics" class="topbar-brand-link" title="Go to Home Dashboard">
                    <figure>
                        <img src="assets/images/ISATU.png" alt="University Logo">
                    </figure>
                    <h1>Office of Student Affairs Services</h1>
                </a>
            </div>
            
            <section class="user-controls">
                <div class="user-info">
                    <p><strong><?php echo htmlspecialchars($displayLastName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <p class="user-role"><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                
                <span class="user-avatar" id="userAvatarBtn" title="User Menu">
                    <?php if (!empty($profilePic) && file_exists($profilePic)): ?>
                        <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="User Avatar" class="user-avatar-img">
                    <?php else: ?>
                        <?php echo strtoupper(substr($displayLastName, 0, 1)); ?>
                    <?php endif; ?>
                </span>

                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header-info">
                        <p><strong><?php echo htmlspecialchars($displayLastName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="user-role"><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <ul>
                        <li>
                            <a href="dashboard.php?page=settings&tab=profile">
                                <span title="Navigate to User Account">Profile</span>
                            </a>
                        </li>
                        <li>
                            <button type="button" id="menuThemeToggleBtn">
                                <span title="Change Theme">Change Theme</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="menuThemeCheckbox">
                                    <span class="toggle-slider">
                                        <span class="toggle-knob"></span>
                                    </span>
                                </label>
                            </button>
                        </li>
                        <li>
                            <button type="button" id="openAccessibilityBtn">
                                <span title="Open Accessibility Options">Accessibility</span>
                            </button>
                        </li>
                        <?php if (has_module_access($role, 'settings', $conn)): ?>
                            <li>
                                <a href="dashboard.php?page=settings">
                                    <span title="Navigate to Settings">Settings</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="logout.php" class="logout-link">
                                <span title="Log Out">Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="header-clock" id="headerClock">
                    <span class="clock-time" id="clockTime">--:--:--</span>
                    <span class="clock-date" id="clockDate">--- --, ----</span>
                </div>
            </section>
        </header>

        <div class="accessibility-bar hide-element" id="accessibilityBar">
            <div class="accessibility-controls-group">
                <div class="accessibility-control-item">
                    <label for="fontFamilySelect">Font Family:</label>
                    <select id="fontFamilySelect">
                        <option value="'Inter', sans-serif">Inter</option>
                        <option value="'Google Sans', sans-serif">Google Sans</option>
                        <option value="'Roboto', sans-serif">Roboto</option>
                        <option value="'Open Sans', sans-serif">Open Sans</option>
                        <option value="system-ui, sans-serif">System UI</option>
                    </select>
                </div>
                <div class="accessibility-control-item">
                    <label for="fontSizeSelect">Font Size:</label>
                    <select id="fontSizeSelect">
                        <option value="0.875">Small (87.5%)</option>
                        <option value="1" selected>Normal (100%)</option>
                        <option value="1.125">Large (112.5%)</option>
                        <option value="1.25">Extra Large (125%)</option>
                    </select>
                </div>
            </div>
            <button type="button" class="accessibility-close-btn" id="closeAccessibilityBtn" title="Close Accessibility Controls">&times;</button>
        </div>

        <section class="content-area">
            <?php 
                $targetFilePath = $allowedPagesMap[$requestedPage];

                if ($isPageAllowed) {
                    if (file_exists($targetFilePath)) {
                        include($targetFilePath);
                    } else {
                        echo "<article class='table-card'><h2>404 Page Not Found</h2><p>The requested view file does not exist on the server.</p></article>";
                    }
                } else {
                    echo "<article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to view this module based on current RBAC configurations.</p></article>";
                }
            ?>
        </section>
    </main>

    <?php 
        // Load reusable components directly from root path
        if (file_exists('modals.php')) include_once 'modals.php';
        if (file_exists('toast.php')) include_once 'toast.php';
    ?>

    <script>
        function updateHeaderClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            document.getElementById('clockTime').textContent = timeStr;
            document.getElementById('clockDate').textContent = dateStr;
        }

        updateHeaderClock();
        setInterval(updateHeaderClock, 1000);

        const menuThemeCheckbox = document.getElementById('menuThemeCheckbox');
        const menuThemeToggleBtn = document.getElementById('menuThemeToggleBtn');
        const currentTheme = localStorage.getItem('themePreference') || 'dark';

        function applyTheme(isLight) {
            if (isLight) {
                document.body.classList.add('light-theme');
                if (menuThemeCheckbox) menuThemeCheckbox.checked = true;
            } else {
                document.body.classList.remove('light-theme');
                if (menuThemeCheckbox) menuThemeCheckbox.checked = false;
            }
        }

        applyTheme(currentTheme === 'light');

        if (menuThemeCheckbox) {
            menuThemeCheckbox.addEventListener('change', (e) => {
                const isLight = e.target.checked;
                localStorage.setItem('themePreference', isLight ? 'light' : 'dark');
                applyTheme(isLight);
            });
        }

        if (menuThemeToggleBtn) {
            menuThemeToggleBtn.addEventListener('click', (e) => {
                if (e.target.closest('.toggle-switch')) return;
                menuThemeCheckbox.checked = !menuThemeCheckbox.checked;
                const isLight = menuThemeCheckbox.checked;
                localStorage.setItem('themePreference', isLight ? 'light' : 'dark');
                applyTheme(isLight);
            });
        }

        const userAvatarBtn = document.getElementById('userAvatarBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        userAvatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!userDropdownMenu.contains(e.target) && e.target !== userAvatarBtn) {
                userDropdownMenu.classList.remove('active');
            }
        });

        const openAccessibilityBtn = document.getElementById('openAccessibilityBtn');
        const accessibilityBar = document.getElementById('accessibilityBar');
        const closeAccessibilityBtn = document.getElementById('closeAccessibilityBtn');
        const fontFamilySelect = document.getElementById('fontFamilySelect');
        const fontSizeSelect = document.getElementById('fontSizeSelect');

        const savedFontFamily = localStorage.getItem('accessibilityFontFamily') || "'Inter', sans-serif";
        const savedFontSize = localStorage.getItem('accessibilityFontSize') || "1";

        document.documentElement.style.setProperty('--app-font-family', savedFontFamily);
        document.documentElement.style.setProperty('--font-scale-factor', savedFontSize);
        
        fontFamilySelect.value = savedFontFamily;
        fontSizeSelect.value = savedFontSize;

        openAccessibilityBtn.addEventListener('click', () => {
            userDropdownMenu.classList.remove('active');
            accessibilityBar.classList.remove('hide-element');
        });

        closeAccessibilityBtn.addEventListener('click', () => {
            accessibilityBar.classList.add('hide-element');
        });

        fontFamilySelect.addEventListener('change', (e) => {
            const val = e.target.value;
            document.documentElement.style.setProperty('--app-font-family', val);
            localStorage.setItem('accessibilityFontFamily', val);
        });

        fontSizeSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            document.documentElement.style.setProperty('--font-scale-factor', val);
            localStorage.setItem('accessibilityFontSize', val);
        });

        const burgerBtn = document.getElementById('burgerBtn');
        const mainSidebar = document.getElementById('mainSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            mainSidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
            burgerBtn.classList.toggle('active');
        }

        burgerBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
            .then(() => console.log("Service Worker Registered"));
        }
    </script>
</body>
</html>