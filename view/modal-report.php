<!-- Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateReportModalLabel">Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- The Report Generation Form -->
                <form id="generateReportForm" method="POST" action="../view/report.php" target="_blank">
                    <div class="mb-3">
                        <label for="class_id" class="form-label">Select Class</label>
                        <select name="class_id" id="class_id" class="form-select" required>
                            <?php
                            include('../config/db.php');

                            $result = $conn->query("SELECT class_id, name FROM classes");
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['class_id']}'>{$row['name']}</option>";
                                }
                            } else {
                                echo "<option disabled>No classes found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="term" class="form-label">Select Term</label>
                        <select name="term" id="term" class="form-select" required>
                            <?php
                            $termResult = $conn->query("SELECT DISTINCT term FROM exams");
                            if ($termResult && $termResult->num_rows > 0) {
                                while ($termRow = $termResult->fetch_assoc()) {
                                    echo "<option value='{$termRow['term']}'>{$termRow['term']}</option>";
                                }
                            } else {
                                echo "<option disabled>No terms found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
