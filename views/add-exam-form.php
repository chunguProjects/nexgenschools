<?php
include('../config/db.php');
include('../model/exam.php');

$examModel = new Exams($conn);
$editing = isset($_GET['exam_id']);
$exam = $editing ? $examModel->getExamById((int)$_GET['exam_id']) : null;

$examName = $exam['exam_name'] ?? '';
$term = $exam['term'] ?? '';
$academicYear = $exam['academic_year'] ?? '';
?>

<div class="container justify-content-center mt-5">
    <h4 class="mb-4"><?= $editing ? 'Edit Exam' : 'Add New Exam' ?></h4>
    <form action="../controllers/exam-controller.php" method="POST" novalidate>
        <?php if ($editing): ?>
            <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
        <?php endif; ?>

        <!-- Exam Name Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required
                       value="<?= htmlspecialchars($examName) ?>"
                       placeholder="Enter exam name">
                <div class="form-text">E.g., Midterm, Final Exam</div>
            </div>
        </div>

        <!-- Term Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="term" class="form-label">Term</label>
                <input type="text" class="form-control" id="term" name="term" required
                       value="<?= htmlspecialchars($term) ?>"
                       placeholder="e.g., Term 1, Term 2">
            </div>
        </div>

        <!-- Academic Year Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <select class="form-select" id="academic_year" name="academic_year" required>
                    <option value="" disabled <?= !$academicYear ? 'selected' : '' ?>>Select Academic Year</option>
                    <?php
                    $currentYear = (int)date('Y');
                    $startYear = $currentYear - 1;
                    $endYear = $currentYear + 4;

                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $next = $year + 1;
                        $label = "{$year}-{$next}";
                        $selected = ($label === $academicYear) ? 'selected' : '';
                        echo "<option value='{$label}' {$selected}>{$label}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Submit and Cancel Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit"
                    name="<?= $editing ? 'update_exam' : 'add_exam' ?>"
                    class="btn btn-primary me-2">
                <?= $editing ? 'Update Exam' : 'Add Exam' ?>
            </button>
            <a href="schooladmin.php?page=exam" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
