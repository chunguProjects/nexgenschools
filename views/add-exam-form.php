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

<div class="container-fluid py-3">

    <!-- PAGE HEADER -->
    <div class="lg-page-header">

        <a href="admin.php?page=exam"
           class="lg-btn-back text-decoration-none">
            <i class="fa fa-arrow-left"></i>
            Back
        </a>

        <h4 class="lg-page-title mb-0">
            <?= $editing ? "Edit Exam" : "Add New Exam" ?>
        </h4>

        <div></div>

    </div>

    <!-- FORM CARD -->
    <div class="lg-form-card">

        <h4>
            <?= $editing ? "Update Exam Details" : "Exam Information" ?>
        </h4>

        <div class="lg-form-divider"></div>

        <form action="../controllers/exam-controller.php" method="POST" novalidate>

            <?php if ($editing): ?>
                <input type="hidden" name="exam_id" value="<?= (int)$exam['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="exam_name">Exam Name</label>
                    <input type="text"
                           class="form-control"
                           id="exam_name"
                           name="exam_name"
                           value="<?= htmlspecialchars($examName) ?>"
                           placeholder="Enter exam name"
                           required>
                    <div class="form-text">E.g., Midterm, Final Exam</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="term">Term</label>
                    <input type="text"
                           class="form-control"
                           id="term"
                           name="term"
                           value="<?= htmlspecialchars($term) ?>"
                           placeholder="e.g., Term 1, Term 2"
                           required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="academic_year_id">Academic Year</label>
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

            <div class="d-flex gap-2 mt-3">

                <button type="submit"
                        name="<?= $editing ? 'update_exam' : 'add_exam' ?>"
                        class="lg-btn-add">
                    <i class="fa fa-save"></i>
                    <?= $editing ? "Update Exam" : "Save Exam" ?>
                </button>

                <a href="admin.php?page=exam"
                   class="lg-btn-back text-decoration-none">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>