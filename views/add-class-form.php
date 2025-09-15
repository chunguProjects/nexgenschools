<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
include('../model/class.php');

// Messages from session (better than GET)
$message = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Determine if editing
$isEdit = false;
$classId = $_GET['class_id'] ?? null;
$classData = [
    'name'           => '',
    'description'    => '',
    'teacher_id'     => '',
    'academic_year_id' => ''
];

$classModel = new ClassGroup($conn);

if ($classId) {
    $isEdit = true;
    $class = $classModel->getClassById($classId);

    if ($class) {
        $classData = [
            'name'            => $class['name'],
            'description'     => $class['description'],
            'teacher_id'      => $class['teacher_id'],
            'academic_year_id'=> $class['academic_year_id']
        ];
    } else {
        $error = "Class not found.";
        $isEdit = false;
    }
}

// Fetch all teachers
$teachers = $conn->query("
    SELECT u.user_id, u.full_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    WHERE r.name = 'teacher'
");

// Fetch all academic years
$academicYears = $conn->query("SELECT academic_year_id, name FROM academic_years ORDER BY start_date DESC");
?>

<div class="container mt-5">
    <h4 class="mb-4"><?= $isEdit ? 'Edit Class' : 'Add New Class' ?></h4>

    <!-- Feedback -->
    <?php if ($message): ?>
        <div class="alert alert-success" id="flash-message"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger" id="flash-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="../controllers/class-controller.php" method="POST">
        <?php if ($isEdit): ?>
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($classId) ?>">
        <?php endif; ?>

        <!-- Class Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Class Name</label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= htmlspecialchars($classData['name']) ?>" placeholder="e.g., Form 1A" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" id="description" name="description"
                   value="<?= htmlspecialchars($classData['description']) ?>" placeholder="e.g., Science stream" required>
        </div>

        <!-- Academic Year -->
        <div class="mb-3">
            <label for="academic_year_id" class="form-label">Academic Year</label>
            <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                <option value="" disabled <?= !$classData['academic_year_id'] ? 'selected' : '' ?>>Select Academic Year</option>
                <?php if ($academicYears->num_rows > 0): ?>
                    <?php while ($year = $academicYears->fetch_assoc()): ?>
                        <option value="<?= $year['academic_year_id'] ?>"
                            <?= ($year['academic_year_id'] == $classData['academic_year_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year['name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>No academic years found</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Class Teacher -->
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Class Teacher</label>
            <select class="form-select" id="teacher_id" name="teacher_id" required>
                <option value="" disabled <?= !$classData['teacher_id'] ? 'selected' : '' ?>>Select Teacher</option>
                <?php if ($teachers->num_rows > 0): ?>
                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                        <option value="<?= $teacher['user_id'] ?>"
                            <?= ($teacher['user_id'] == $classData['teacher_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($teacher['full_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>No teachers found</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" name="<?= $isEdit ? 'update_class' : 'add_class' ?>" class="btn btn-primary me-2">
                <?= $isEdit ? 'Update Class' : 'Add Class' ?>
            </button>
            <a href="admin.php?page=class" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    // Auto-disappear flash message after 3 seconds
    const flash = document.getElementById('flash-message');
    if (flash) {
        setTimeout(() => { flash.style.display = 'none'; }, 3000);
    }
</script>
