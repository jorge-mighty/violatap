<?php
// import_students.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn)) {
    require_once 'db_config.php';
}
if (!function_exists('log_activity')) {
    require_once 'audit_helper.php';
}

$currentRole = trim($_SESSION['role'] ?? '');
if (!in_array($currentRole, ['Superadmin', 'COD Admin'], true)) {
    header("Location: dashboard.php?page=student_nfc_management&error=" . urlencode("Unauthorized access to CSV import."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (!isset($_FILES['student_csv']) || $_FILES['student_csv']['error'] !== UPLOAD_ERR_OK) {
        header("Location: dashboard.php?page=student_nfc_management&error=" . urlencode("Please upload a valid CSV file."));
        exit();
    }

    $fileTmpPath = $_FILES['student_csv']['tmp_name'];
    $handle = fopen($fileTmpPath, "r");
    if ($handle === false) {
        header("Location: dashboard.php?page=student_nfc_management&error=" . urlencode("Could not read uploaded CSV file."));
        exit();
    }

    $count = 0;
    // 11 parameters: 9 strings ('s'), 1 integer ('i'), 1 string ('s') -> "ssssssssis"
    $stmt = $conn->prepare("INSERT IGNORE INTO students (student_id_no, first_name, middlename, last_name, gender, course, major, department, year_level, section, is_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

    if ($stmt) {
        $isFirstRow = true;
        $hasIndexColumn = false;

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_map('trim', $row);
            
            if (empty(array_filter($row))) {
                continue; 
            }

            if ($isFirstRow) {
                $firstCol = strtolower($row[0] ?? '');
                if ($firstCol === '#' || $firstCol === 'no' || $firstCol === 'no.' || $firstCol === 'index' || $firstCol === 'id') {
                    $hasIndexColumn = true;
                }
                $isFirstRow = false;
            }

            if ($hasIndexColumn || (isset($row[0]) && ctype_digit($row[0]) && count($row) >= 11)) {
                array_shift($row);
            }

            $colZero = strtolower($row[0] ?? '');
            $colOne = strtolower($row[1] ?? '');
            if (strpos($colZero, 'school') !== false || strpos($colOne, 'first') !== false || strpos($colZero, 'student_id') !== false) {
                continue;
            }

            if (count($row) < 10) {
                continue;
            }

            $student_id_no = $row[0] ?? '';
            $first_name    = $row[1] ?? '';
            $middlename    = $row[2] ?? '';
            $last_name     = $row[3] ?? '';
            $gender        = $row[4] ?? 'Male';
            $course        = $row[5] ?? 'BSIT';
            $major         = !empty($row[6]) ? $row[6] : null;
            $department    = $row[7] ?? 'Computer Studies Department';
            $year_level    = (int)($row[8] ?? 1);
            $section       = $row[9] ?? '';

            if (!empty($student_id_no) && !empty($first_name) && !empty($last_name)) {
                // Corrected bind_param types: 9 strings, 1 integer, 1 string ("ssssssssis")
                $stmt->bind_param("ssssssssis", $student_id_no, $first_name, $middlename, $last_name, $gender, $course, $major, $department, $year_level, $section);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $count++;
                }
            }
        }
        $stmt->close();
    }

    fclose($handle);

    $currentUserId = $_SESSION['user_id'] ?? 0;
    log_activity($conn, $currentUserId, 'Bulk Students CSV Import', "Successfully imported {$count} student records via CSV upload.");

    header("Location: dashboard.php?page=student_nfc_management&msg=" . urlencode("Successfully imported {$count} students."));
    exit();
}

header("Location: dashboard.php?page=student_nfc_management");
exit();
?>