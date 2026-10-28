<div class="container-fluid py-3">

  <!-- Page Header -->
  <div class="lg-page-header">
    <a href="admin.php?page=dashboard" class="lg-btn-back">
      <i class="fa fa-arrow-left"></i> Back
    </a>
    <h4 class="lg-page-title mb-0">Grading Criteria</h4>
    <div></div>
  </div>

  <!-- Level Buttons -->
  <div class="d-flex gap-2 mb-3">
    <button type="button" class="btn btn-ledger-outline level-button" data-level="JCE" onclick="selectLevel('JCE')">JCE</button>
    <button type="button" class="btn btn-ledger-outline level-button" data-level="MSCE" onclick="selectLevel('MSCE')">MSCE</button>
  </div>

  <input type="hidden" id="level" value="">

  <!-- Form -->
  <form id="gradingForm">

    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-hover lg-table">
          <thead>
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
    </div>

    <div class="d-flex gap-2 mt-3 mb-3">
      <button type="button" class="btn btn-ledger-outline" onclick="addCriteriaRow()">+ Add Row</button>
      <button type="submit" class="lg-btn-add">Save Criteria</button>
    </div>

  </form>

  <div id="statusMessage" class="gs-toast-wrap"></div>
</div>

<script>
let deletedIds = []; // IMPORTANT: track deleted DB rows

function selectLevel(level) {
  document.getElementById('level').value = level;

  document.querySelectorAll('.level-button').forEach(btn => {
    btn.classList.remove('btn-ledger-solid');
    btn.classList.add('btn-ledger-outline');
  });

  const activeBtn = document.querySelector(`[data-level="${level}"]`);
  if (activeBtn) {
    activeBtn.classList.remove('btn-ledger-outline');
    activeBtn.classList.add('btn-ledger-solid');
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
      <input type="hidden" name="id[]" value="${c.id ?? ''}">
      <input type="text" name="grade[]" class="form-control" value="${c.grade ?? ''}" required>
    </td>

    <td><input type="number" name="min_marks[]" class="form-control" value="${c.min_marks ?? ''}" required></td>
    <td><input type="number" name="max_marks[]" class="form-control" value="${c.max_marks ?? ''}" required></td>
    <td><input type="text" name="description[]" class="form-control" value="${c.description ?? ''}"></td>
    <td><input type="text" name="form_teacher_remark[]" class="form-control" value="${c.form_teacher_remark ?? ''}"></td>
    <td><input type="text" name="head_teacher_remark[]" class="form-control" value="${c.head_teacher_remark ?? ''}"></td>

    <td>
      <button type="button" class="btn btn-sm lg-btn-delete">Remove</button>
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
    `<div class="alert alert-success alert-dismissible fade show">${data.message}
       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
     </div>`;

  deletedIds = [];

  // reload table data
  loadCriteria(document.getElementById('level').value);

  // auto-hide success message after 3 seconds
  setTimeout(() => {
    const alert = msgBox.querySelector('.alert');

    if (alert) {
      alert.classList.remove('show');
      setTimeout(() => {
        msgBox.innerHTML = '';
      }, 300);
    }
  }, 3000);
})
  .catch(err => {
    document.getElementById('statusMessage').innerHTML =
      `<div class="alert alert-danger alert-dismissible fade show">${err.message}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
       </div>`;
  });
});

/* DEFAULT LOAD */
document.addEventListener('DOMContentLoaded', () => {
  selectLevel('JCE');
});
</script>