<?php
ini_set('display_errors', 0);
error_reporting(0);

include('../config/db.php');
include('../model/class.php');
include('../model/academic_year.php');

$classModel   = new ClassGroup($conn);
$yearModel    = new AcademicYear($conn);

$currentYear = $yearModel->getCurrentYear();
$allYears    = $yearModel->getAll();
$allClasses  = $classModel->getAllClasses($currentYear['academic_year_id']);
?>

<div class="container my-4">
    <h2 class="mb-4">Promote Students</h2>

    <form id="promotion-form" class="p-4 bg-light border rounded shadow-sm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="current_class_id" class="form-label fw-semibold">Current Class:</label>
                <select name="current_class_id" id="current_class_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($allClasses as $class): ?>
                        <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="target_academic_year_id" class="form-label fw-semibold">Target Academic Year:</label>
                <select name="target_academic_year_id" id="target_academic_year_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php while ($year = $allYears->fetch_assoc()): ?>
                        <option value="<?= $year['academic_year_id'] ?>"><?= htmlspecialchars($year['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="target_class_id" class="form-label fw-semibold">Target Class:</label>
                <select name="target_class_id" id="target_class_id" class="form-select" required>
                    <option value="0">-- Auto-generate if none --</option>
                </select>
            </div>
        </div>

        <!-- Student List -->
        <div id="student-list" class="mb-3"></div>

        <button type="submit" id="promote-btn" class="btn btn-primary">
            <i class="fa fa-level-up-alt me-2"></i> Promote Selected
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const currentClassSelect = document.getElementById('current_class_id');
    const targetYearSelect = document.getElementById('target_academic_year_id');
    const targetClassSelect = document.getElementById('target_class_id');
    const studentListDiv = document.getElementById('student-list');
    const promotionForm = document.getElementById('promotion-form');

    // Load students from enrollments (enhanced card view)
    function loadStudents(classId, yearId = null) {
        if (!classId) {
            studentListDiv.innerHTML = '';
            return;
        }

        let url = `../controllers/get-students-by-class.php?class_id=${classId}`;
        if (yearId) url += `&target_academic_year_id=${yearId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.length) {
                    studentListDiv.innerHTML = '<p class="text-muted">No students in this class.</p>';
                    return;
                }

                let html = `<div class="card shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Select Students</span>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                        <label class="form-check-label fw-semibold mb-0" for="select-all">Select All</label>
                                    </div>
                                </div>
                                <div class="card-body p-2" style="max-height: 250px; overflow-y: auto;">`;

                data.forEach(student => {
                    const disabled = student.already_promoted ? 'disabled' : '';
                    const badge = student.already_promoted ? `<span class="badge bg-success ms-2">Already Promoted</span>` : '';
                    html += `<div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="student_ids[]" value="${student.student_id}" id="student-${student.student_id}" ${disabled}>
                                <label class="form-check-label" for="student-${student.student_id}">
                                    ${student.first_name} ${student.last_name} ${badge}
                                </label>
                             </div>`;
                });

                html += `</div></div>`;
                studentListDiv.innerHTML = html;

                // Select All only for non-disabled checkboxes
                document.getElementById('select-all').addEventListener('change', function() {
                    const checked = this.checked;
                    document.querySelectorAll('input[name="student_ids[]"]:not(:disabled)').forEach(cb => cb.checked = checked);
                });
            })
            .catch(err => {
                console.error(err);
                studentListDiv.innerHTML = '<p class="text-danger">Error loading students.</p>';
            });
    }

    // Load students when current class changes (current academic year)
    currentClassSelect.addEventListener('change', function() {
        const classId = this.value;
        loadStudents(classId);
    });

    // Load target classes when target academic year changes (working logic)
targetYearSelect.addEventListener('change', function() {
    const yearId = this.value;
    const currentClassId = currentClassSelect.value;

    targetClassSelect.innerHTML = '<option value="">-- Select --</option>';

    if (!yearId || !currentClassId) return;

    fetch(`../controllers/get-target-class.php?current_class_id=${currentClassId}&target_year_id=${yearId}`)
        .then(res => res.json())
        .then(data => {
            targetClassSelect.innerHTML = ''; // clear first

            if (data.success && data.classes.length > 0) {
                // Populate all returned classes
                data.classes.forEach(cls => {
                    const option = document.createElement('option');
                    option.value = cls.class_id;
                    option.textContent = cls.name;
                    targetClassSelect.appendChild(option);
                });

                // Auto-select the first one
                targetClassSelect.value = data.classes[0].class_id;
            } else {
                targetClassSelect.innerHTML = '<option value="">No classes found</option>';
            }
        })
        .catch(err => {
            console.error(err);
            targetClassSelect.innerHTML = '<option value="">Error loading classes</option>';
        });
});

    // Handle promotion form submission
    promotionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = document.getElementById('promote-btn');
        btn.disabled = true;
        btn.textContent = 'Promoting...';

        fetch('../controllers/promote-students.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                btn.disabled = false;
                btn.textContent = 'Promote Selected';
                if (data.success) location.reload();
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Promote Selected';
                alert('Something went wrong.');
            });
    });

});
</script>

