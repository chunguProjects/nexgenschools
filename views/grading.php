<div class="container mt-4">

  <!-- Back Button -->
  <div class="row align-items-center mb-3">
    <div class="col-auto">
      <a href="admin.php?page=dashboard" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Back
      </a>
    </div>
    <div class="col text-center">
      <h4 class="mb-0">Grading Criteria</h4>
    </div>
    <div class="col-auto"></div>
  </div>

  <!-- Level Buttons -->
  <div class="d-flex gap-2 mb-3">
    <button type="button" class="btn btn-outline-primary level-button" data-level="JCE" onclick="selectLevel('JCE')">JCE</button>
    <button type="button" class="btn btn-outline-success level-button" data-level="MSCE" onclick="selectLevel('MSCE')">MSCE</button>
  </div>

  <input type="hidden" id="level" value="">

  <!-- Form -->
  <form id="gradingForm">

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Grade</th>
            <th>Min Marks</th>
            <th>Max Marks</th>
            <th>Description</th>
            <th>Form Teacher Remark</th>
            <th>Head Teacher Remark</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody id="criteriaTable"></tbody>
      </table>
    </div>

    <div class="d-flex gap-2 mb-3">
      <button type="button" class="btn btn-secondary" onclick="addCriteriaRow()">+ Add Row</button>
      <button type="submit" class="btn btn-primary">Save Criteria</button>
    </div>

  </form>

  <div id="statusMessage"></div>
</div>

<script>
let deletedIds = []; // IMPORTANT: track deleted DB rows

function selectLevel(level) {
  document.getElementById('level').value = level;

  document.querySelectorAll('.level-button').forEach(btn => {
    btn.classList.remove('active', 'btn-primary', 'btn-success');
  });

  const activeBtn = document.querySelector(`[data-level="${level}"]`);
  if (activeBtn) {
    activeBtn.classList.add('active');
    activeBtn.classList.add(level === 'JCE' ? 'btn-primary' : 'btn-success');
  }

  deletedIds = []; // reset when switching level
  loadCriteria(level);
}

/* ================= LOAD ================= */
function loadCriteria(level) {
  fetch(`../controllers/gradingCriteriaController.php?level=${encodeURIComponent(level)}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('criteriaTable');
      tbody.innerHTML = '';

      if (!Array.isArray(data)) return;

      data.forEach(c => {
        tbody.appendChild(createRow(c));
      });

      if (data.length === 0) {
        addCriteriaRow();
      }
    });
}

/* ================= CREATE ROW ================= */
function createRow(c = {}) {
  const row = document.createElement('tr');

  row.innerHTML = `
    <td>
      <input type="hidden" name="id[]" value="${c.id || ''}">
      <input type="text" name="grade[]" class="form-control" value="${c.grade || ''}" required>
    </td>

    <td><input type="number" name="min_marks[]" class="form-control" value="${c.min_marks || ''}" required></td>
    <td><input type="number" name="max_marks[]" class="form-control" value="${c.max_marks || ''}" required></td>
    <td><input type="text" name="description[]" class="form-control" value="${c.description || ''}"></td>
    <td><input type="text" name="form_teacher_remark[]" class="form-control" value="${c.form_teacher_remark || ''}"></td>
    <td><input type="text" name="head_teacher_remark[]" class="form-control" value="${c.head_teacher_remark || ''}"></td>

    <td>
      <button type="button" class="btn btn-sm btn-danger">Remove</button>
    </td>
  `;

  // remove handler
  row.querySelector('button').addEventListener('click', () => {
    const id = row.querySelector('input[name="id[]"]').value;

    if (id) {
      deletedIds.push(id); // store DB ID for deletion
    }

    row.remove();
  });

  return row;
}

/* ================= ADD ROW ================= */
function addCriteriaRow() {
  document.getElementById('criteriaTable').appendChild(createRow());
}

/* ================= SUBMIT ================= */
document.getElementById('gradingForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append('level', document.getElementById('level').value);

  // send deleted IDs
  deletedIds.forEach(id => {
    formData.append('deleted_ids[]', id);
  });

  fetch('../controllers/gradingCriteriaController.php', {
    method: 'POST',
    body: formData
  })
  .then(async res => {
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    return data;
  })
  .then(data => {
  const msgBox = document.getElementById('statusMessage');

  msgBox.innerHTML =
    `<div class="alert alert-success">${data.message}</div>`;

  deletedIds = [];

  // reload table data
  loadCriteria(document.getElementById('level').value);

  // auto-hide success message after 3 seconds
  setTimeout(() => {
    const alert = msgBox.querySelector('.alert');

    if (alert) {
      alert.style.transition = "opacity 0.5s ease";
      alert.style.opacity = "0";

      setTimeout(() => {
        msgBox.innerHTML = '';
      }, 500);
    }
  }, 3000);
})
  .catch(err => {
    document.getElementById('statusMessage').innerHTML =
      `<div class="alert alert-danger">${err.message}</div>`;
  });
});

/* DEFAULT LOAD */
document.addEventListener('DOMContentLoaded', () => {
  selectLevel('JCE');
});
</script>
