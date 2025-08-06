<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
include('../model/class.php');

// Get current school_id
$schoolId = $_SESSION['user']['school_id'] ?? null;

// Get messages
$message = $_GET['message'] ?? null;
$error   = $_GET['error'] ?? null;

// Determine if editing
$isEdit = false;
$classId = $_GET['class_id'] ?? null;
$classData = [
    'name'        => '',
    'description' => '',
    'teacher_id'  => ''
];

if ($classId && $schoolId) {
    $isEdit = true;
    $classModel = new ClassGroup($conn);
    $class = $classModel->getClassById($classId);

    if ($class && $class['school_id'] == $schoolId) {
        $classData = [
            'name'        => $class['name'],
            'description' => $class['description'],
            'teacher_id'  => $class['teacher_id']
        ];
    } else {
        $error = "Class not found or unauthorized access.";
        $isEdit = false;
    }
}
?>

<div class="container mt-5">
    <h4 class="mb-4"><?= $isEdit ? 'Edit Class' : 'Add New Class' ?></h4>

    <!-- Feedback -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="../controllers/class-controller.php" method="POST">
        <?php if ($isEdit): ?>
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($classId) ?>">
        <?php endif; ?>

        <!-- Class Name -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($classData['name']) ?>" placeholder="e.g., Form 1A" required>
            </div>
        </div>

        <!-- Description -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($classData['description']) ?>" placeholder="e.g., Science stream" required>
            </div>
        </div>

        <!-- Class Teacher -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="teacher_id" class="form-label">Class Teacher</label>
                <select class="form-select" id="teacher_id" name="teacher_id" required>
                    <option value="" disabled <?= !$classData['teacher_id'] ? 'selected' : '' ?>>Select Teacher</option>
                    <?php
                    if ($schoolId) {
                        $roleStmt = $conn->prepare("SELECT role_id FROM roles WHERE name = 'teacher'");
                        $roleStmt->execute();
                        $roleResult = $roleStmt->get_result();

                        if ($roleRow = $roleResult->fetch_assoc()) {
                            $teacherRoleId = $roleRow['role_id'];
                            $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE role_id = ? AND school_id = ?");
                            $stmt->bind_param("ii", $teacherRoleId, $schoolId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($teacher = $result->fetch_assoc()) {
                                $selected = ($teacher['user_id'] == $classData['teacher_id']) ? 'selected' : '';
                                echo "<option value='{$teacher['user_id']}' $selected>{$teacher['full_name']}</option>";
                            }

                            if ($result->num_rows === 0) {
                                echo "<option value='' disabled>No teachers found</option>";
                            }
                        } else {
                            echo "<option value='' disabled>Teacher role not found</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" name="<?= $isEdit ? 'update_class' : 'add_class' ?>" class="btn btn-primary me-2">
                <?= $isEdit ? 'Update Class' : 'Add Class' ?>
            </button>
            <a href="schooladmin.php?page=class" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
