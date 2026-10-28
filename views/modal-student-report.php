<style>
  #studentReportModal .modal-header{
    background: var(--green-dark, #124F32);
    color: #fff;
    border-bottom: 3px solid var(--gold, #C7962C);
  }
  #studentReportModal .modal-title{
    font-family: 'Fraunces', serif;
    font-weight: 600;
  }
  #studentReportModal .btn-close{ filter: invert(1) grayscale(1) brightness(2); }
  #studentReportModal .form-label{
    font-size: 12.5px;
    font-weight: 600;
    color: var(--muted, #6B6459);
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  #studentReportModal .form-select:focus,
  #studentReportModal .form-control:focus{
    border-color: var(--green, #1C7A4D);
    box-shadow: 0 0 0 .2rem rgba(28,122,77,0.15);
  }
</style>

<!-- Modal -->
<div class="modal fade" id="studentReportModal" tabindex="-1" aria-labelledby="studentReportModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width:500px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="studentReportModalLabel">Student Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="studentReportForm" method="POST" action="../controllers/student-report-controller.php" target="_blank">

            <!-- Academic Year -->
            <div class="mb-3">
                <label class="form-label">Select Academic Year</label>
                <select name="academic_year_id" id="sr_academic_year_id" class="form-select" required>
                    <option value="" selected disabled>-- Select Academic Year --</option>
                    <?php
                    if (!isset($conn)) {
                        include_once(__DIR__ . '/../config/db.php');
                    }
                    if (session_status() === PHP_SESSION_NONE) session_start();

                    $stmt = $conn->prepare("SELECT academic_year_id, name FROM academic_years ORDER BY name DESC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['academic_year_id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Class -->
            <div class="mb-3">
                <label class="form-label">Select Class</label>
                <select name="class_id" id="sr_class_id" class="form-select" required disabled>
                    <option value="">Select academic year first</option>
                </select>
            </div>

            <!-- Term -->
            <div class="mb-3">
                <label class="form-label">Select Term</label>
                <select name="term" id="sr_term" class="form-select" required disabled>
                    <option value="">Select academic year first</option>
                </select>
            </div>

            <!-- Exam -->
            <div class="mb-3">
                <label class="form-label">Select Exam</label>
                <select name="exam_id" id="sr_exam_id" class="form-select" required disabled>
                    <option value="">Select academic year first</option>
                </select>
            </div>

            <!-- Student (searchable) -->
            <div class="mb-3">
                <label class="form-label">Search Student</label>
                <input type="text" id="sr_student_search" class="form-control mb-2"
                       placeholder="Type a student's name..." autocomplete="off" disabled>
                <select name="student_id" id="sr_student_id" class="form-select" required disabled size="6" style="display:none;">
                </select>
                <div id="sr_student_hint" class="form-text">Select a class first.</div>
            </div>

            <button type="submit" class="btn btn-ledger-solid w-100" id="sr_submit_btn" disabled>Generate Report</button>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
    const yearSelect = document.getElementById('sr_academic_year_id');
    const classSelect = document.getElementById('sr_class_id');
    const termSelect = document.getElementById('sr_term');
    const examSelect = document.getElementById('sr_exam_id');
    const studentSearch = document.getElementById('sr_student_search');
    const studentSelect = document.getElementById('sr_student_id');
    const studentHint = document.getElementById('sr_student_hint');
    const submitBtn = document.getElementById('sr_submit_btn');

    let allStudents = [];

    function resetDownstreamOf(level) {
        if (level <= 1) {
            classSelect.innerHTML = '<option value="">Select academic year first</option>';
            classSelect.disabled = true;
            termSelect.innerHTML = '<option value="">Select academic year first</option>';
            termSelect.disabled = true;
            examSelect.innerHTML = '<option value="">Select academic year first</option>';
            examSelect.disabled = true;
        }
        allStudents = [];
        studentSelect.innerHTML = '';
        studentSelect.style.display = 'none';
        studentSelect.disabled = true;
        studentSearch.value = '';
        studentSearch.disabled = true;
        studentHint.textContent = 'Select a class first.';
        submitBtn.disabled = true;
    }

    yearSelect.addEventListener('change', function () {
        resetDownstreamOf(1);
        const yearId = this.value;
        if (!yearId) return;

        fetch(`../controllers/get-academic-data.php?academic_year_id=${yearId}`)
            .then(res => res.json())
            .then(data => {
                classSelect.disabled = false;
                termSelect.disabled = false;
                examSelect.disabled = false;

                classSelect.innerHTML = '<option value="">Select Class</option>';
                termSelect.innerHTML = '<option value="">Select Term</option>';
                examSelect.innerHTML = '<option value="">Select Exam</option>';

                data.classes.forEach(c => {
                    classSelect.innerHTML += `<option value="${c.class_id}">${c.name}</option>`;
                });
                data.terms.forEach(t => {
                    termSelect.innerHTML += `<option value="${t.term}">${t.term}</option>`;
                });
                data.exams.forEach(e => {
                    examSelect.innerHTML += `<option value="${e.id}">${e.exam_name}</option>`;
                });
            });
    });

    classSelect.addEventListener('change', function () {
        const classId = this.value;
        allStudents = [];
        studentSelect.innerHTML = '';
        studentSelect.style.display = 'none';
        studentSearch.value = '';
        submitBtn.disabled = true;

        if (!classId) {
            studentSearch.disabled = true;
            studentHint.textContent = 'Select a class first.';
            return;
        }

        studentHint.textContent = 'Loading students...';
        studentSearch.disabled = true;

        fetch(`../controllers/get-students-by-class.php?class_id=${classId}`)
            .then(res => res.json())
            .then(data => {
                allStudents = Array.isArray(data.students) ? data.students : [];
                studentSearch.disabled = false;
                studentHint.textContent = allStudents.length
                    ? `${allStudents.length} student(s) in this class. Start typing to search.`
                    : 'No students found in this class.';
            })
            .catch(() => {
                studentHint.textContent = 'Failed to load students for this class.';
            });
    });

    studentSearch.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        submitBtn.disabled = true;

        if (!q) {
            studentSelect.style.display = 'none';
            studentSelect.innerHTML = '';
            return;
        }

        const matches = allStudents.filter(s =>
            `${s.first_name} ${s.last_name}`.toLowerCase().includes(q)
        );

        studentSelect.innerHTML = matches
            .map(s => `<option value="${s.student_id}">${s.first_name} ${s.last_name}</option>`)
            .join('');

        studentSelect.style.display = matches.length ? 'block' : 'none';
        studentSelect.disabled = matches.length === 0;
    });

    studentSelect.addEventListener('change', function () {
        submitBtn.disabled = !this.value;
        const selected = studentSelect.options[studentSelect.selectedIndex];
        if (selected) studentSearch.value = selected.textContent;
        studentSelect.style.display = 'none';
    });
})();
</script>