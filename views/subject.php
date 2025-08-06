<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php');
include('../model/subject.php');

// Check if user is logged in and has a school_id
if (!isset($_SESSION['user']['school_id'])) {
    die("Unauthorized access.");
}

$schoolId = $_SESSION['user']['school_id'];
$subjectModel = new Subjects($conn);
$subjects = $subjectModel->getAllSubjects($schoolId);

// Feedback message handling
$message = $_GET['message'] ?? null;
$error = $_GET['error'] ?? null;
?>

<div class="container-fluid py-3">
    <!-- Page Heading and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <a href="schooladmin.php?page=dashboard" class="btn btn-outline-secondary me-3">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
        <a href="schooladmin.php?page=add-subject-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Subject
        </a>
    </div>

    <!-- Feedback messages -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Subject List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Subject List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Subject Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= $subject['subject_id'] ?></td>
                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                                <td><?= htmlspecialchars($subject['teacher_name']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary editSubjectBtn" data-id="<?= $subject['subject_id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteSubjectBtn" data-id="<?= $subject['subject_id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No Subjects Found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteSubjectForm" method="POST" action="../controllers/subject-controller.php" style="display: none;">
    <input type="hidden" name="delete_subject" value="1">
    <input type="hidden" name="subject_id" id="delete_subject_id">
</form>

<script>
document.querySelectorAll('.editSubjectBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const subjectId = this.getAttribute('data-id');
        window.location.href = `schooladmin.php?page=add-subject-form&subject_id=${subjectId}`;
    });
});

document.querySelectorAll('.deleteSubjectBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const subjectId = this.getAttribute('data-id');

        if (confirm("Are you sure you want to delete this subject?")) {
            document.getElementById('delete_subject_id').value = subjectId;
            document.getElementById('deleteSubjectForm').submit();
        }
    });
});
</script>
