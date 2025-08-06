<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('../config/db.php');
include('../model/subject.php');

// Ensure user is logged in and has a school_id
if (!isset($_SESSION['user']['school_id'])) {
    die("Unauthorized access.");
}

$schoolId = $_SESSION['user']['school_id'];
$subjectId = $_GET['subject_id'] ?? null;

$subjectData = [
    'subject_name' => '',
    'subject_code' => '',
    'teacher_id'   => '',
];

$isEdit = false;

if ($subjectId) {
    $isEdit = true;
    $subjectModel = new Subjects($conn);
    $subject = $subjectModel->getSubjectById((int)$subjectId);

    if (!$subject) {
        die("Subject not found.");
    }

    $subjectData = [
        'subject_name' => $subject['subject_name'],
        'subject_code' => $subject['subject_code'],
        'teacher_id'   => $subject['teacher_id'],
    ];
}

// Feedback messages
$message = $_GET['message'] ?? null;
$error = $_GET['error'] ?? null;
?>

<div class="container justify-content-center mt-5">
    <h4 class="mb-4"><?= $isEdit ? "Edit Subject" : "Add New Subject" ?></h4>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="../controllers/subject-controller.php" method="POST">
        <?php if ($isEdit): ?>
            <input type="hidden" name="update_subject" value="1">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">
        <?php else: ?>
            <input type="hidden" name="add_subject" value="1">
        <?php endif; ?>

        <!-- Subject Name -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="subject_name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name"
                       value="<?= htmlspecialchars($subjectData['subject_name']) ?>" required>
            </div>
        </div>

        <!-- Subject Code -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="subject_code" class="form-label">Subject Code</label>
                <input type="text" class="form-control" id="subject_code" name="subject_code"
                       value="<?= htmlspecialchars($subjectData['subject_code']) ?>" required>
            </div>
        </div>

        <!-- Subject Teacher -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="teacher_id" class="form-label">Subject Teacher</label>
                <select class="form-select" id="teacher_id" name="teacher_id" required>
                    <option value="" disabled <?= !$subjectData['teacher_id'] ? 'selected' : '' ?>>Select Teacher</option>
                    <?php
                    $query = "SELECT user_id, full_name FROM users WHERE role_id = 3 AND school_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $schoolId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($teacher = $result->fetch_assoc()):
                        $selected = $teacher['user_id'] == $subjectData['teacher_id'] ? 'selected' : '';
                        echo "<option value='{$teacher['user_id']}' $selected>{$teacher['full_name']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" class="btn btn-primary me-2"><?= $isEdit ? "Update" : "Add" ?> Subject</button>
            <a href="schooladmin.php?page=subject" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
