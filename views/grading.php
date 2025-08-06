<div class="container mt-4">
  <!-- Back Button -->
 <div class="row align-items-center mb-3">
  <div class="col-auto">
    <a href="schooladmin.php?page=dashboard" class="btn btn-outline-secondary">
      <i class="fa fa-arrow-left"></i> Back
    </a>
  </div>
  <div class="col text-center">
    <h4 class="mb-0">Grading Criteria</h4>
  </div>
  <div class="col-auto"></div> <!-- Placeholder to balance layout -->
</div>


  <!-- Level Selection -->
  <div class="d-flex gap-2 mb-3">
    <button type="button" class="btn btn-outline-primary level-button" data-level="JCE" onclick="selectLevel('JCE')">JCE</button>
    <button type="button" class="btn btn-outline-success level-button" data-level="MSCE" onclick="selectLevel('MSCE')">MSCE</button>
  </div>

  <!-- Hidden level input -->
  <input type="hidden" id="level" name="level" value="">

  <!-- Grading Form -->
  <form id="gradingForm">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Grade</th>
            <th>Min Marks</th>
            <th>Max Marks</th>
            <th>Teacher Remark</th>
            <th>Form Teacher Remark</th>
            <th>Head Teacher Remark</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="criteriaTable">
          <!-- Dynamic rows will be added here -->
        </tbody>
      </table>
    </div>

    <div class="mb-3">
      <button type="button" class="btn btn-secondary" onclick="addCriteriaRow()">+ Add Row</button>
      <button type="submit" class="btn btn-primary">Save Criteria</button>
    </div>
  </form>

  <div id="statusMessage" class="mt-3"></div>
</div>

<script>
function selectLevel(level) {
  document.getElementById('level').value = level;

  const buttons = document.querySelectorAll('.btn-outline-primary, .btn-outline-success');
  buttons.forEach(btn => btn.classList.remove('active', 'btn-primary', 'btn-success'));

  const activeBtn = Array.from(buttons).find(btn => btn.textContent.trim() === level);
  if (activeBtn) {
    activeBtn.classList.add('active');
    activeBtn.classList.add(level === 'JCE' ? 'btn-primary' : 'btn-success');
  }

  // Load existing criteria for selected level
  loadCriteria(level);
}

function loadCriteria(level) {
  fetch(`../controllers/gradingCriteriaController.php?level=${encodeURIComponent(level)}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('criteriaTable');
      tbody.innerHTML = ''; // Clear table

      if (!Array.isArray(data)) {
        console.error("Unexpected data format:", data);
        return;
      }

      data.forEach(c => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td><input type="text" name="grade[]" class="form-control" value="${c.grade}" required></td>
          <td><input type="number" name="min_marks[]" class="form-control" value="${c.min_marks}" required></td>
          <td><input type="number" name="max_marks[]" class="form-control" value="${c.max_marks}" required></td>
          <td><input type="text" name="description[]" class="form-control" value="${c.description || ''}"></td>
          <td><input type="text" name="form_teacher_remark[]" class="form-control" value="${c.form_teacher_remark || ''}"></td>
          <td><input type="text" name="head_teacher_remark[]" class="form-control" value="${c.head_teacher_remark || ''}"></td>
          <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>
        `;
        tbody.appendChild(row);
      });

      if (data.length === 0) addCriteriaRow(); // If no records, add an empty row
    })
    .catch(err => {
      console.error("Failed to load grading criteria:", err);
      document.getElementById('statusMessage').innerHTML = `<div class="alert alert-danger">Failed to load grading data.</div>`;
    });
}


function addCriteriaRow() {
  const tbody = document.getElementById('criteriaTable');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input type="text" name="grade[]" class="form-control" required></td>
    <td><input type="number" name="min_marks[]" class="form-control" required></td>
    <td><input type="number" name="max_marks[]" class="form-control" required></td>
    <td><input type="text" name="description[]" class="form-control"></td>
    <td><input type="text" name="form_teacher_remark[]" class="form-control"></td>
    <td><input type="text" name="head_teacher_remark[]" class="form-control"></td>
    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>
  `;
  tbody.appendChild(row);
}

// Submit form via AJAX
document.getElementById('gradingForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);
  formData.append('level', document.getElementById('level').value);

  fetch('../controllers/gradingCriteriaController.php', {
    method: 'POST',
    body: formData
  })

.then(async res => {
  const data = await res.json();
  if (!res.ok) {
    throw new Error(data.error || 'Unknown error occurred');
  }
  return data;
})
.then(data => {
  const statusDiv = document.getElementById('statusMessage');
  statusDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
})
.catch(err => {
  document.getElementById('statusMessage').innerHTML = `<div class="alert alert-danger">Request failed: ${err.message}</div>`;
  console.error("Form submission error:", err);
});

});

// On load, set default level and first row
document.addEventListener('DOMContentLoaded', function () {
  selectLevel('JCE');
  addCriteriaRow();
});
</script>
