<?php
// File: export-parent-credentials.php
session_start();
include('../config/db.php');

// Ensure school admin is logged in
$schoolId = $_SESSION['user']['school_id'] ?? null;
if (!$schoolId) {
    die("Unauthorized access");
}

// CSV Headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=parent_credentials.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output CSV column headers
fputcsv($output, ['Parent Name', 'Phone Number (Username)', 'Temporary Password', 'Student Name']);

$query = "
    SELECT 
        u.full_name AS parent_name,
        u.phone_number AS username,
        u.password AS hashed_password,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name
    FROM users u
    INNER JOIN parent_student ps ON u.user_id = ps.parent_id
    INNER JOIN students s ON ps.student_id = s.student_id
    WHERE u.role_id = (
        SELECT role_id FROM roles WHERE name = 'parent' LIMIT 1
    )
    AND u.school_id = ?
    ORDER BY u.full_name
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $schoolId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // You can't decrypt hashed passwords. If you saved the plaintext temp password separately, use that.
    // Otherwise, use a placeholder like "changeme123"
    fputcsv($output, [
        $row['parent_name'],
        $row['username'],
        'changeme123',
        $row['student_name']
    ]);
}

fclose($output);
exit;
