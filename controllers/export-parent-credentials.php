<?php
session_start();
include('../config/db.php');

$schoolId = $_SESSION['user']['school_id'] ?? null;
if (!$schoolId) {
    die("Unauthorized access");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=parent_credentials.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Parent Name', 'Phone Number (Username)', 'Temporary Password', 'Student Name']);

// 1. Load parents from session first (newly created)
$sessionParents = $_SESSION['parent_credentials'] ?? [];

// Use username as key to avoid duplicates
$exportedUsernames = [];
foreach ($sessionParents as $entry) {
    fputcsv($output, [
        $entry['parent_name'],
        $entry['username'],
        $entry['password'],
        $entry['student_name']
    ]);
    $exportedUsernames[$entry['username']] = true;
}

// 2. Fetch all parents in DB excluding those already exported
$query = "
    SELECT 
        u.full_name AS parent_name,
        u.phone_number AS username,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name
    FROM users u
    INNER JOIN parent_student ps ON u.user_id = ps.parent_id
    INNER JOIN students s ON ps.student_id = s.student_id
    WHERE u.role_id = (SELECT role_id FROM roles WHERE name = 'parent' LIMIT 1)
    AND u.school_id = ?
    ORDER BY u.full_name
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $schoolId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if (!isset($exportedUsernames[$row['username']])) {
        fputcsv($output, [
            $row['parent_name'],
            $row['username'],
            '******', // Placeholder for password
            $row['student_name']
        ]);
        $exportedUsernames[$row['username']] = true;
    }
}

fclose($output);
exit;
