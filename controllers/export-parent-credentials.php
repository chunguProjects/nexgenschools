<?php
session_start();
include('../config/db.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=student_details.csv');

$output = fopen('php://output', 'w');

// CSV headers
fputcsv($output, ['Registration Number','Student Name','Date of Birth', 'Class']);

// 1. Load newly created students from session (if any)
$sessionStudents = $_SESSION['student_credentials'] ?? [];
$exportedUsernames = [];

foreach ($sessionStudents as $entry) {
    fputcsv($output, [
        $entry['username'],
        $entry['first_name'] . ' ' . $entry['last_name'],
        $entry['password'],
        $entry['date_of_birth'] ?? '',
        $entry['class_name'] ?? ''
    ]);
    $exportedUsernames[$entry['username']] = true;
}

// 2. Fetch all students from DB excluding session ones and soft-deleted
$query = "
    SELECT 
        s.first_name,
        s.last_name,
        s.username,
        s.date_of_birth,
        c.name AS class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE s.is_deleted = 0
    ORDER BY s.first_name, s.last_name
";


$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    if (!isset($exportedUsernames[$row['username']])) {
        fputcsv($output, [
            $row['username'],
            $row['first_name'] . ' ' . $row['last_name'],
            $row['date_of_birth'],
            $row['class_name'] ?? ''
        ]);
        $exportedUsernames[$row['username']] = true;
    }
}

fclose($output);
exit;
