<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    echo "<article class='table-card'><h2>Access Restricted</h2><p>Please log in to continue.</p></article>";
    exit();
}

$currentRole = trim($_SESSION['role'] ?? '');
$normRole    = normalize_role_name($currentRole);

$hasAccess = has_module_access($currentRole, 'dashboard', $conn);
if (!$hasAccess && $normRole !== 'superadmin' && $normRole !== 'csoadmin' && $normRole !== 'cso' && $normRole !== 'codadmin' && $normRole !== 'cod') {
    echo "<article class='table-card'><h2>Access Restricted</h2><p>You do not have permission to view the Analytics Dashboard.</p></article>";
    exit();
}

date_default_timezone_set('Asia/Manila');
if (isset($conn) && $conn instanceof mysqli) {
    $conn->query("SET time_zone = '+08:00'");
}

$displayLabels = ['Computer Studies', 'Teacher Education', 'BindTech', 'HBM'];

$deptMap = [
    'Computer Studies Department' => 'Computer Studies',
    'Teacher Education Department' => 'Teacher Education',
    'Industrial Technology Department' => 'BindTech',
    'Hospitality and Business Management Department' => 'HBM'
];

$currentMonth = (int)date('n');
$currentCalendarYear = (int)date('Y');

if ($currentMonth >= 8) {
    $thisAyStart = $currentCalendarYear;
    $thisAyEnd   = $currentCalendarYear + 1;
    $thisSemLabel = "1st Sem (AY {$thisAyStart}-{$thisAyEnd})";
    $thisSemStart = "{$currentCalendarYear}-08-01 00:00:00";
    $thisSemEnd   = "{$currentCalendarYear}-12-31 23:59:59";
    $lastSemLabel = "2nd Sem (AY " . ($thisAyStart - 1) . "-{$thisAyStart})";
    $lastSemStart = "{$currentCalendarYear}-01-01 00:00:00";
    $lastSemEnd   = "{$currentCalendarYear}-05-31 23:59:59";
} else {
    $thisAyStart = $currentCalendarYear - 1;
    $thisAyEnd   = $currentCalendarYear;
    $thisSemLabel = "2nd Sem (AY {$thisAyStart}-{$thisAyEnd})";
    $thisSemStart = "{$currentCalendarYear}-01-01 00:00:00";
    $thisSemEnd   = "{$currentCalendarYear}-05-31 23:59:59";
    $lastSemLabel = "1st Sem (AY {$thisAyStart}-{$thisAyEnd})";
    $lastSemStart = "{$thisAyStart}-08-01 00:00:00";
    $lastSemEnd   = "{$thisAyStart}-12-31 23:59:59";
}

$lastAyStart = $thisAyStart - 1;
$lastAyEnd   = $thisAyStart;

$filterMode    = $_GET['filter_mode'] ?? 'today';
$selectedMonth = $_GET['selected_month'] ?? date('Y-m');
$selectedWeek  = $_GET['selected_week'] ?? date('Y-\WW');
$selectedDay   = $_GET['selected_day'] ?? date('Y-m-d');
$startDate     = $_GET['start_date'] ?? '';
$endDate       = $_GET['end_date'] ?? '';
$viewMode      = $_GET['view_mode'] ?? 'dept_gender';

$whereClauses = [];

if ($filterMode === 'today') {
    $whereClauses[] = "DATE(vr.incident_date) = CURDATE()";
} elseif ($filterMode === 'this_year') {
    $whereClauses[] = "vr.incident_date >= '{$thisAyStart}-08-01 00:00:00' AND vr.incident_date <= '{$thisAyEnd}-05-31 23:59:59'";
} elseif ($filterMode === 'last_year') {
    $whereClauses[] = "vr.incident_date >= '{$lastAyStart}-08-01 00:00:00' AND vr.incident_date <= '{$lastAyEnd}-05-31 23:59:59'";
} elseif ($filterMode === 'this_semester') {
    $whereClauses[] = "vr.incident_date >= '{$thisSemStart}' AND vr.incident_date <= '{$thisSemEnd}'";
} elseif ($filterMode === 'last_semester') {
    $whereClauses[] = "vr.incident_date >= '{$lastSemStart}' AND vr.incident_date <= '{$lastSemEnd}'";
} elseif ($filterMode === 'month' && !empty($selectedMonth)) {
    $mStart = date('Y-m-01 00:00:00', strtotime($selectedMonth . '-01'));
    $mEnd   = date('Y-m-t 23:59:59', strtotime($selectedMonth . '-01'));
    $whereClauses[] = "vr.incident_date >= '{$mStart}' AND vr.incident_date <= '{$mEnd}'";
} elseif ($filterMode === 'week' && !empty($selectedWeek)) {
    $wStart = date('Y-m-d 00:00:00', strtotime($selectedWeek));
    $wEnd   = date('Y-m-d 23:59:59', strtotime($selectedWeek . ' +6 days'));
    $whereClauses[] = "vr.incident_date >= '{$wStart}' AND vr.incident_date <= '{$wEnd}'";
} elseif ($filterMode === 'day' && !empty($selectedDay)) {
    $dClean = $conn->real_escape_string($selectedDay);
    $whereClauses[] = "DATE(vr.incident_date) = '{$dClean}'";
} elseif ($filterMode === 'custom' && !empty($startDate) && !empty($endDate)) {
    $sClean = $conn->real_escape_string($startDate);
    $eClean = $conn->real_escape_string($endDate);
    $whereClauses[] = "DATE(vr.incident_date) BETWEEN '{$sClean}' AND '{$eClean}'";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(' AND ', $whereClauses) : "";

$rangeDisplay = "All Time";
if ($filterMode === 'today') {
    $rangeDisplay = "Today (" . date('M d, Y') . ")";
} elseif ($filterMode === 'month') {
    $rangeDisplay = date('F Y', strtotime($selectedMonth . '-01'));
} elseif ($filterMode === 'week') {
    $rangeDisplay = "Week of " . date('M d, Y', strtotime($selectedWeek));
} elseif ($filterMode === 'day') {
    $rangeDisplay = date('M d, Y', strtotime($selectedDay));
} elseif ($filterMode === 'custom' && !empty($startDate) && !empty($endDate)) {
    $rangeDisplay = date('m/d/Y', strtotime($startDate)) . " - " . date('m/d/Y', strtotime($endDate));
} elseif ($filterMode === 'this_year') {
    $rangeDisplay = "AY {$thisAyStart}-{$thisAyEnd}";
} elseif ($filterMode === 'last_year') {
    $rangeDisplay = "AY {$lastAyStart}-{$lastAyEnd}";
} elseif ($filterMode === 'this_semester') {
    $rangeDisplay = $thisSemLabel;
} elseif ($filterMode === 'last_semester') {
    $rangeDisplay = $lastSemLabel;
}

// Consolidated Aggregation Query
$metricsAggQuery = "SELECT COUNT(*) as total_count, 
                           SUM(CASE WHEN vr.status = 'For Verification' OR vr.status = 'Unsettled' THEN 1 ELSE 0 END) as pending_count 
                    FROM violation_records vr 
                    JOIN students s ON vr.student_uid = s.student_uid {$whereSql}";

$metricsRes = $conn->query($metricsAggQuery);
$metricsData = $metricsRes ? $metricsRes->fetch_assoc() : ['total_count' => 0, 'pending_count' => 0];

$totalVPeriod   = (int)($metricsData['total_count'] ?? 0);
$pendingVPeriod = (int)($metricsData['pending_count'] ?? 0);

$settledVPeriod = $totalVPeriod - $pendingVPeriod;
$settlementRate = $totalVPeriod > 0 ? round(($settledVPeriod / $totalVPeriod) * 100, 1) : 0;

if ($filterMode === 'all') {
    $offensesReportText = "Across the cumulative timeframe, total recorded infractions stand at <strong>{$totalVPeriod}</strong>.";
} elseif ($filterMode === 'today') {
    $offensesReportText = "Today, total recorded infractions stand at <strong>{$totalVPeriod}</strong>.";
} elseif ($filterMode === 'this_year' || $filterMode === 'last_year') {
    $offensesReportText = "During the {$rangeDisplay} period, total recorded offenses reached <strong>{$totalVPeriod}</strong> incidents.";
} else {
    $offensesReportText = "During the selected period ({$rangeDisplay}), total recorded infractions stand at <strong>{$totalVPeriod}</strong>.";
}

$groupedSql = "SELECT s.department, s.gender, COUNT(vr.record_id) as total_count FROM violation_records vr JOIN students s ON vr.student_uid = s.student_uid {$whereSql} GROUP BY s.department, s.gender";
$result = $conn->query($groupedSql);

$maleCounts = ['Computer Studies' => 0, 'Teacher Education' => 0, 'BindTech' => 0, 'HBM' => 0];
$femaleCounts = ['Computer Studies' => 0, 'Teacher Education' => 0, 'BindTech' => 0, 'HBM' => 0];
$deptTotalViolations = ['Computer Studies' => 0, 'Teacher Education' => 0, 'BindTech' => 0, 'HBM' => 0];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $mappedDept = $deptMap[$row['department']] ?? 'Computer Studies';
        $gender = ucfirst(strtolower($row['gender'] ?? ''));
        $count = (int)$row['total_count'];

        if (isset($deptTotalViolations[$mappedDept])) {
            $deptTotalViolations[$mappedDept] += $count;
            if ($gender === 'Male') {
                $maleCounts[$mappedDept] += $count;
            } elseif ($gender === 'Female') {
                $femaleCounts[$mappedDept] += $count;
            }
        }
    }
}

$deptOnlyValues = [];
$maleValues = [];
$femaleValues = [];
foreach ($displayLabels as $label) {
    $deptOnlyValues[] = $deptTotalViolations[$label];
    $maleValues[] = $maleCounts[$label];
    $femaleValues[] = $femaleCounts[$label];
}

$topDeptQ = $conn->query("SELECT s.department, COUNT(*) as c FROM violation_records vr JOIN students s ON vr.student_uid = s.student_uid {$whereSql} GROUP BY s.department ORDER BY c DESC LIMIT 1")->fetch_assoc();
$topTypeQ = $conn->query("SELECT cd.offense, COUNT(*) as c FROM violation_records vr JOIN students s ON vr.student_uid = s.student_uid JOIN code_of_discipline cd ON vr.offense_id = cd.offense_id {$whereSql} GROUP BY cd.offense_id ORDER BY c DESC LIMIT 1")->fetch_assoc();

$rawTopDept = $topDeptQ['department'] ?? 'N/A';
$topDept = $deptMap[$rawTopDept] ?? $rawTopDept;
$topDeptCount = $topDeptQ['c'] ?? 0;
$topType = $topTypeQ['offense'] ?? 'N/A';
$topTypeCount = $topTypeQ['c'] ?? 0;
?>

<section class="admin-container fit-screen">
    <header class="app-header header-compact">
        <div class="header-titles">
            <h2>Analytics Dashboard</h2>
            <p>Selected Period: <strong><?php echo htmlspecialchars($rangeDisplay, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        </div>

        <form method="GET" action="dashboard.php" class="range-filter-inline" id="unifiedAnalyticsForm">
            <input type="hidden" name="page" value="analytics">
            <input type="hidden" name="filter_mode" id="filter_mode_input" value="<?php echo htmlspecialchars($filterMode, ENT_QUOTES, 'UTF-8'); ?>">

            <button type="button" class="range-btn <?php echo $filterMode === 'today' ? 'active' : ''; ?>" onclick="setFilterMode('today')">Today</button>
            
            <input type="date" name="selected_day" class="date-input" value="<?php echo htmlspecialchars($selectedDay, ENT_QUOTES, 'UTF-8'); ?>" onchange="setFilterMode('day')" title="Filter by Specific Day">
            <input type="week" name="selected_week" class="date-input" value="<?php echo htmlspecialchars($selectedWeek, ENT_QUOTES, 'UTF-8'); ?>" onchange="setFilterMode('week')" title="Filter by Specific Week">
            <input type="month" name="selected_month" class="date-input" value="<?php echo htmlspecialchars($selectedMonth, ENT_QUOTES, 'UTF-8'); ?>" onchange="setFilterMode('month')" title="Filter by Specific Month">

            <button type="button" class="range-btn <?php echo $filterMode === 'this_semester' ? 'active' : ''; ?>" onclick="setFilterMode('this_semester')">This Semester</button>
            <button type="button" class="range-btn <?php echo $filterMode === 'last_semester' ? 'active' : ''; ?>" onclick="setFilterMode('last_semester')">Last Semester</button>
            <button type="button" class="range-btn <?php echo $filterMode === 'this_year' ? 'active' : ''; ?>" onclick="setFilterMode('this_year')">This Year</button>
            <button type="button" class="range-btn <?php echo $filterMode === 'last_year' ? 'active' : ''; ?>" onclick="setFilterMode('last_year')">Last Year</button>
            <button type="button" class="range-btn <?php echo $filterMode === 'all' ? 'active' : ''; ?>" onclick="setFilterMode('all')">All Time</button>
        </form>
    </header>

    <div class="metrics-container">
        <article class="metric-card primary">
            <h3>Total Recorded Offenses</h3>
            <p class="stat-number"><?php echo $totalVPeriod; ?></p>
        </article>

        <article class="metric-card warning">
            <h3>Pending Violations</h3>
            <p class="stat-number"><?php echo $pendingVPeriod; ?></p>
        </article>

        <article class="metric-card bottom-accent">
            <div class="stat-dual-wrapper">
                <div class="stat-block">
                    <h3 class="stat-label">Settled Cases</h3>
                    <p class="stat-number"><?php echo $settledVPeriod; ?></p>
                </div>
                <div class="stat-block">
                    <h3 class="stat-label">Resolution Rate</h3>
                    <p class="stat-number"><?php echo $settlementRate; ?>%</p>
                </div>
            </div>
        </article>
    </div>

    <div class="hero-analytics-grid">
        <article class="chart-card hero-chart-card" id="printableChartArea">
            <header class="chart-card-header">
                <h3>Violation Distribution</h3>
                
                <select name="view_mode" id="chartViewSelect" form="unifiedAnalyticsForm" class="filter-select-sm" onchange="this.form.submit()">
                    <option value="dept_gender" <?php echo $viewMode === 'dept_gender' ? 'selected' : ''; ?>>Dept & Gender</option>
                    <option value="dept_only" <?php echo $viewMode === 'dept_only' ? 'selected' : ''; ?>>Dept Only</option>
                </select>
            </header>

            <figure class="chart-container">
                <canvas id="analyticsChart"></canvas>
            </figure>
        </article>

        <div class="report-column-wrapper">
            <div class="external-actions-header">
                <button type="button" class="btn btn-primary portrait-action-btn" onclick="printAnalyticsReport()">
                    <img src="assets/icons/outline/printer.svg" alt="Print" class="asset-icon-img">
                    <span>Print</span>
                </button>
                <button type="button" class="btn btn-secondary portrait-action-btn" onclick="exportAnalyticsData()">
                    <img src="assets/icons/outline/download.svg" alt="Export" class="asset-icon-img">
                    <span>Export</span>
                </button>
            </div>

            <article class="chart-card report-card hero-chart-card" id="printableReportArea">
                <h3>Descriptive Analytics Report</h3>

                <div class="analytics-report">
                    <div class="inner-report-card">
                        <span class="report-tag">TOTAL RECORDED OFFENSES</span>
                        <p class="quote"><?php echo $offensesReportText; ?></p>
                    </div>

                    <div class="inner-report-card">
                        <span class="report-tag">RESOLUTION EFFICIENCY</span>
                        <p class="quote">Settled cases stand at <strong><?php echo $settledVPeriod; ?></strong> (<strong><?php echo $settlementRate; ?>%</strong> resolution rate), with <strong><?php echo $pendingVPeriod; ?></strong> case(s) currently pending resolution or verification.</p>
                    </div>

                    <div class="inner-report-card">
                        <span class="report-tag">PRIMARY DEPARTMENT CONCERN</span>
                        <p class="quote"><strong><?php echo htmlspecialchars($topDept, ENT_QUOTES, 'UTF-8'); ?></strong> accounts for the highest infraction volume within the selected period (<?php echo htmlspecialchars($rangeDisplay, ENT_QUOTES, 'UTF-8'); ?>), recording <strong><?php echo $topDeptCount; ?></strong> violation(s).</p>
                    </div>

                    <div class="inner-report-card">
                        <span class="report-tag">MOST FREQUENT INFRACTION</span>
                        <p class="quote"><strong><?php echo htmlspecialchars($topType, ENT_QUOTES, 'UTF-8'); ?></strong> is the most common breach across all departments, totaling <strong><?php echo $topTypeCount; ?></strong> case(s).</p>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<script>
    function setFilterMode(mode) {
        document.getElementById('filter_mode_input').value = mode;
        document.getElementById('unifiedAnalyticsForm').submit();
    }

    function initAnalyticsChart() {
        const canvasElement = document.getElementById('analyticsChart');
        if (!canvasElement) return;

        if (window.analyticsChartInstance) {
            window.analyticsChartInstance.destroy();
        }

        const isLight = document.body.classList.contains('light-theme');
        const textColor = isLight ? '#1e293b' : 'rgba(248, 250, 252, 0.85)';
        const gridColor = isLight ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.08)';

        const ctx = canvasElement.getContext('2d');
        const cleanLabels = <?php echo json_encode($displayLabels); ?>;
        const deptOnlyData = <?php echo json_encode($deptOnlyValues); ?>;
        const maleData = <?php echo json_encode($maleValues); ?>;
        const femaleData = <?php echo json_encode($femaleValues); ?>;

        const deptGradient = ctx.createLinearGradient(0, 0, 0, 300);
        deptGradient.addColorStop(0, 'rgba(15, 43, 92, 0.9)');
        deptGradient.addColorStop(1, 'rgba(30, 58, 138, 0.75)');

        const maleGradient = ctx.createLinearGradient(0, 0, 0, 300);
        maleGradient.addColorStop(0, 'rgba(15, 43, 92, 0.9)');
        maleGradient.addColorStop(1, 'rgba(30, 58, 138, 0.75)');

        const femaleGradient = ctx.createLinearGradient(0, 0, 0, 300);
        femaleGradient.addColorStop(0, 'rgba(245, 158, 11, 0.9)');
        femaleGradient.addColorStop(1, 'rgba(251, 191, 36, 0.75)');

        const datasetsMode = {
            dept_gender: [
                { label: 'Male', data: maleData, backgroundColor: maleGradient, borderColor: '#3b82f6', borderWidth: 1, borderRadius: 0 },
                { label: 'Female', data: femaleData, backgroundColor: femaleGradient, borderColor: '#fbbf24', borderWidth: 1, borderRadius: 0 }
            ],
            dept_only: [
                { label: 'Total Violations', data: deptOnlyData, backgroundColor: deptGradient, borderColor: '#f59e0b', borderWidth: 1, borderRadius: 0 }
            ]
        };

        const initialMode = '<?php echo htmlspecialchars($viewMode, ENT_QUOTES, 'UTF-8'); ?>';

        requestAnimationFrame(function() {
            window.analyticsChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cleanLabels,
                    datasets: datasetsMode[initialMode] || datasetsMode.dept_gender
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            let delay = 0;
                            if (context.type === 'data') {
                                delay = (context.dataIndex * 120) + (context.datasetIndex * 80);
                            }
                            return delay;
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: textColor, font: { size: 12, weight: '600' } }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: textColor, font: { weight: '600' } } },
                        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } }
                    }
                }
            });
        });
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initAnalyticsChart();
    } else {
        document.addEventListener("DOMContentLoaded", initAnalyticsChart);
    }

    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            setTimeout(initAnalyticsChart, 50);
        });
    }

    function printAnalyticsReport() { window.print(); }
    function exportAnalyticsPDF() { window.print(); }
    function exportAnalyticsData() { window.print(); }
</script>