<!-- Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width:500px;">
    <div class="modal-content" style="min-height:500px;">

      <div class="modal-header">
        <h5 class="modal-title" id="generateReportModalLabel">Generate Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="generateReportForm" method="POST" action="../controllers/report-controller.php" target="_blank">
          <?php
          include_once(__DIR__ . '/../config/db.php');
          if (session_status() === PHP_SESSION_NONE) session_start();
          ?>

         <!-- Select Academic Year -->
           <div class="mb-3">
          <label class="form-label">Select Academic Year</label>

          <select name="academic_year_id" id="academic_year_id" class="form-select" required>

          <option value="" selected disabled>-- Select Academic Year --</option>

          <?php
          $stmt = $conn->prepare("
          SELECT academic_year_id, name 
          FROM academic_years 
          ORDER BY name DESC
          ");

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
            <select name="class_id" id="class_id" class="form-select" required>
            <option value="">Select academic year first</option>
            </select>
            </div>

            <!-- Term -->
            <div class="mb-3">
            <label class="form-label">Select Term</label>
            <select name="term" id="term" class="form-select" required>
            <option value="">Select academic year first</option>
            </select>
            </div>

            <!-- Exam -->
            <div class="mb-3">
            <label class="form-label">Select Exam</label>
            <select name="exam_id" id="exam_id" class="form-select" required>
            <option value="">Select academic year first</option>
            </select>
            </div>


          <button type="submit" class="btn btn-ledger-solid w-100">Generate Report</button>
        </form>
      </div>

    </div>
  </div>
</div>
<script>

document.addEventListener("change", function(e){

if(e.target && e.target.id === "academic_year_id"){

let year = e.target.value;

fetch("../controllers/get-academic-data.php?academic_year_id="+year)
.then(res => res.json())
.then(data => {

let classSelect = document.getElementById("class_id");
let termSelect = document.getElementById("term");
let examSelect = document.getElementById("exam_id");

classSelect.disabled = false;
termSelect.disabled = false;
examSelect.disabled = false;

classSelect.innerHTML = "<option value=''>Select Class</option>";
termSelect.innerHTML = "<option value=''>Select Term</option>";
examSelect.innerHTML = "<option value=''>Select Exam</option>";

data.classes.forEach(c=>{
classSelect.innerHTML += `<option value="${c.class_id}">${c.name}</option>`;
});

data.terms.forEach(t=>{
termSelect.innerHTML += `<option value="${t.term}">${t.term}</option>`;
});

data.exams.forEach(e=>{
examSelect.innerHTML += `<option value="${e.id}">${e.exam_name}</option>`;
});

});

}

});

</script>