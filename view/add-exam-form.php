<div class="container justify-content-center mt-5">
    <h4 class="mb-4">Add New Exam</h4>
    <form action="../controllers/exam-controller.php" method="POST">
        <!-- Exam Name Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required>
            </div>
        </div>

        <!-- Term Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="term" class="form-label">Term</label>
                <input type="text" class="form-control" id="term" name="term" required>
            </div>
        </div>

        <!-- Academic Year Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <select class="form-select" id="academic_year" name="academic_year" required>
                    <option value="" selected>Select Academic Year</option>
                    <?php
                    // Generate year range dynamically
                    $currentYear = date('Y');
                    $endYear = $currentYear + 5; // Adjust to show desired range
                    for ($startYear = $currentYear; $startYear <= $endYear; $startYear++) {
                        $nextYear = $startYear + 1;
                        echo "<option value='{$startYear}-{$nextYear}'>{$startYear}-{$nextYear}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Submit and Cancel Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" name="add_exam" class="btn btn-warning me-2">Add Exam</button>
            <a href="index.php?page=exam" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
