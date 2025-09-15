<?php
include('../config/db.php');
include('../model/exam.php');

$examModel = new Exams($conn);
$editing = isset($_GET['exam_id']);
$exam = $editing ? $examModel->getExamById((int)$_GET['exam_id']) : null;

$examName       = $exam['exam_name'] ?? '';
$term           = $exam['term'] ?? '';
$academicYearId = $exam['academic_year_id'] ?? '';

// Fetch academic years from database
$academicYears = $conn->query("SELECT academic_year_id, name FROM academic_years ORDER BY start_date DESC");
?>

<div class="container justify-content-center mt-5">
    <h4 class="mb-4"><?= $editing ? 'Edit Exam' : 'Add New Exam' ?></h4>
    <form action="../controllers/exam-controller.php" method="POST" novalidate>
        <?php if ($editing): ?>
            <input type="hidden" name="exam_id" value="<?= (int)$exam['id'] ?>">
        <?php endif; ?>

        <!-- Exam Name Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required
                       value="<?= htmlspecialchars($examName) ?>" placeholder="Enter exam name">
                <div class="form-text">E.g., Midterm, Final Exam</div>
            </div>
        </div>

        <!-- Term Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="term" class="form-label">Term</label>
                <input type="text" class="form-control" id="term" name="term" required
                       value="<?= htmlspecialchars($term) ?>" placeholder="e.g., Term 1, Term 2">
            </div>
        </div>

        <!-- Academic Year Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="academic_year_id" class="form-label">Academic Year</label>
                <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                    <option value="" disabled <?= !$academicYearId ? 'selected' : '' ?>>Select Academic Year</option>
                    <?php
                    if ($academicYears && $academicYears->num_rows > 0) {
                        while ($row = $academicYears->fetch_assoc()) {
                            $selected = ($row['academic_year_id'] == $academicYearId) ? 'selected' : '';
                            echo "<option value='{$row['academic_year_id']}' {$selected}>{$row['name']}</option>";
                        }
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
            <a href="admin.php?page=exam" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
