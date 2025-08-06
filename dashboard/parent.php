<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'parent') {
    header("Location: ../views/login.php?error=Unauthorized Access");
    exit();
}

include('../config/db.php');

$parentId = $_SESSION['user']['user_id'];

// Fetch linked children
$query = "
    SELECT s.student_id, s.first_name, s.last_name, s.gender, s.date_of_birth, c.name AS class_name
    FROM parent_student ps
    JOIN students s ON ps.student_id = s.student_id
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE ps.parent_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $parentId);
$stmt->execute();
$result = $stmt->get_result();
$children = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></h3>

        <?php if (empty($children)): ?>
            <div class="alert alert-info">No students linked to your account.</div>
        <?php else: ?>
            <?php foreach ($children as $child): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
                    </div>
                    <div class="card-body">
                        <p><strong>Gender:</strong> <?php echo $child['gender']; ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo $child['date_of_birth'] ?: 'N/A'; ?></p>
                        <p><strong>Class:</strong> <?php echo $child['class_name'] ?: 'Unassigned'; ?></p>

                        <!-- 📊 View Grades Link -->
                        <a href="view-grades.php?student_id=<?php echo $child['student_id']; ?>" class="btn btn-outline-primary">
                            View Grades
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
