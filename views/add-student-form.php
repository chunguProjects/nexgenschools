<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="container mt-5">
    <!-- 🔙 Back Button -->
    <div class="d-flex align-items-center mb-4">
        <a href="schooladmin.php?page=class" class="btn btn-outline-secondary me-3">
            <i class="fa fa-arrow-left"></i> Back to Classes
        </a>
        <h4 class="mb-0">Enroll Students to Class</h4>
    </div>

    <!-- 👇 Enrollment Form -->
    <form action="../controllers/student-controller.php" method="POST">
        <input type="hidden" name="class_id" value="<?php echo $_GET['class_id']; ?>" />

        <div class="table-responsive">
            <table class="table table-bordered" id="studentTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Parent Phone</th>
                        <th>Relationship</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- First Student Row -->
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="first_name[]" class="form-control" required></td>
                        <td><input type="text" name="last_name[]" class="form-control" required></td>
                        <td>
                            <select name="gender[]" class="form-select" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </td>
                        <td><input type="text" name="parent_phone[]" class="form-control" required></td>
                        <td>
                            <select name="relationship[]" class="form-select">
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian">Guardian</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="mt-3 d-flex gap-2">
            <button type="button" id="addRow" class="btn btn-success">
                <i class="fa fa-plus"></i> Add Student Row
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fa fa-save"></i> Save All Students
            </button>
        </div>
    </form>
</div>

<!-- ✅ JavaScript for Dynamic Rows -->
<script>
    document.getElementById('addRow').addEventListener('click', function () {
        const table = document.getElementById('studentTable').getElementsByTagName('tbody')[0];
        const rowCount = table.rows.length;
        const newRow = table.insertRow();

        newRow.innerHTML = `
            <td>${rowCount + 1}</td>
            <td><input type="text" name="first_name[]" class="form-control" required></td>
            <td><input type="text" name="last_name[]" class="form-control" required></td>
            <td>
                <select name="gender[]" class="form-select" required>
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </td>
            <td><input type="text" name="parent_phone[]" class="form-control" required></td>
            <td>
                <select name="relationship[]" class="form-select">
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Guardian">Guardian</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
            </td>
        `;

        // Attach remove event
        newRow.querySelector('.remove-row').addEventListener('click', function () {
            newRow.remove();
            updateRowNumbers();
        });

        updateRowNumbers();
    });

    // Remove row on click
    document.querySelectorAll('.remove-row').forEach(button => {
        button.addEventListener('click', function () {
            const row = button.closest('tr');
            row.remove();
            updateRowNumbers();
        });
    });

    // Update numbering after removing rows
    function updateRowNumbers() {
        const rows = document.querySelectorAll("#studentTable tbody tr");
        rows.forEach((row, index) => {
            row.cells[0].innerText = index + 1;
        });
    }
</script>
