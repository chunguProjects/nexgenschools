<div class="container mt-5">
    <h4 class="mb-4">Add Students to Class</h4>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addRow" class="btn btn-success mb-3">Add Row</button>
        <button type="submit" class="btn btn-warning">Save Students</button>
    </form>
</div>

<script>
    document.getElementById('addRow').addEventListener('click', function() {
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
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
            </td>
        `;

        // Add event listener for remove button
        newRow.querySelector('.remove-row').addEventListener('click', function() {
            table.deleteRow(newRow.rowIndex - 1);
        });
    });

    // Handle row removal for existing rows
    document.querySelectorAll('.remove-row').forEach(button => {
        button.addEventListener('click', function() {
            const row = button.closest('tr');
            row.parentNode.removeChild(row);
        });
    });
</script>
