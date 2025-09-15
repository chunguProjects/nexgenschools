<style>
  /* Increase modal height and set a max width */
  #generateReportModal .modal-dialog {
    max-width: 500px;
  }
  #generateReportModal .modal-content {
    min-height: 380px;
  }
</style>

<!-- Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="generateReportModalLabel">Generate Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="generateReportForm" method="POST" action="../controllers/report-controller.php" target="_blank">
          <?php
          include('../config/db.php');

          if (session_status() === PHP_SESSION_NONE) {
            session_start();
          }
          ?>

          <!-- Select Class -->
          <div class="mb-3">
            <label for="class_id" class="form-label">Select Class</label>
            <select name="class_id" id="class_id" class="form-select" required>
              <?php
              $stmtClass = $conn->prepare("SELECT class_id, name FROM classes");
              if ($stmtClass->execute()) {
                $resultClass = $stmtClass->get_result();
                if ($resultClass->num_rows > 0) {
                  while ($row = $resultClass->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['class_id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                  }
                } else {
                  echo "<option disabled>No classes found</option>";
                }
              } else {
                echo "<option disabled>Error fetching classes</option>";
              }
              $stmtClass->close();
              ?>
            </select>
          </div>

          <!-- Select Term -->
          <div class="mb-3">
            <label for="term" class="form-label">Select Term</label>
            <select name="term" id="term" class="form-select" required>
              <?php
              $stmtTerm = $conn->prepare("SELECT DISTINCT term FROM exams");
              if ($stmtTerm->execute()) {
                $resultTerm = $stmtTerm->get_result();
                if ($resultTerm->num_rows > 0) {
                  while ($termRow = $resultTerm->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($termRow['term']) . "'>" . htmlspecialchars($termRow['term']) . "</option>";
                  }
                } else {
                  echo '<option value="1">Term 1</option>';
                  echo '<option value="2">Term 2</option>';
                  echo '<option value="3">Term 3</option>';
                }
              } else {
                echo '<option disabled>Error fetching terms</option>';
              }
              $stmtTerm->close();
              ?>
            </select>
          </div>

          <!-- Select Exam -->
          <div class="mb-3">
            <label for="exam_id" class="form-label">Select Exam</label>
            <select name="exam_id" id="exam_id" class="form-select" required>
              <?php
              $stmtExam = $conn->prepare("SELECT id, exam_name FROM exams");
              if ($stmtExam->execute()) {
                $resultExam = $stmtExam->get_result();
                if ($resultExam->num_rows > 0) {
                  while ($exam = $resultExam->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($exam['id']) . "'>" . htmlspecialchars($exam['exam_name']) . "</option>";
                  }
                } else {
                  echo "<option disabled>No exams found</option>";
                }
              } else {
                echo "<option disabled>Error fetching exams</option>";
              }
              $stmtExam->close();
              ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary w-100">Generate Report</button>
        </form>
      </div>

    </div>
  </div>
</div>
