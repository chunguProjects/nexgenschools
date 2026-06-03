<?php
// Start session early
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include dependencies
include('../config/db.php');
include('../model/subject.php');

// --- SESSION CHECK ---
$userId = $_SESSION['user']['id'] ?? null; // admin user

// --- MODEL CALL ---
$subjectModel = new Subjects($conn);
$subjects = $subjectModel->getAllSubjects(); // single-tenant: no school_id

// --- FEEDBACK MESSAGES ---
$message = $_GET['message'] ?? null;
$error   = $_GET['error'] ?? null;
?>

<div class="container-fluid py-3">
    <!-- Page Heading and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="admin.php?page=dashboard" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <a href="admin.php?page=add-subject-form" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Subject
        </a>
    </div>

    <!-- Feedback Messages -->
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
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= $subject['subject_id'] ?></td>
                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                <td><?= htmlspecialchars($subject['subject_code'] ?? '-') ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary editSubjectBtn"
                                data-id="<?= $subject['subject_id'] ?>">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-danger deleteSubjectBtn"
                                data-id="<?= $subject['subject_id'] ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No Subjects Found</td>
                        </tr>
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
        window.location.href = `admin.php?page=add-subject-form&subject_id=${subjectId}`;
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
