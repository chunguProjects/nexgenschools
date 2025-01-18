<div class="container justify-content-center mt-5">
    <h4 class="mb-4">Add New Class</h4>
    <form action="../controllers/class-controller.php" method="POST">
        <!-- Class Name Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
        </div>

        <!-- Description Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" required>
            </div>
        </div>

        <!-- Class Teacher Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="teacher_id" class="form-label">Class Teacher</label>
                <select class="form-select" id="teacher_id" name="teacher_id" required>
                    <option value="" disabled selected>Select Teacher</option>
                    <?php
                    // Include the database connection file
                    include('../config/db.php');

                    // Fetch teachers from the users table
                    $query = "SELECT user_id, full_name FROM users WHERE role = 'teacher'";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($teacher = $result->fetch_assoc()) {
                            echo "<option value='{$teacher['user_id']}'>{$teacher['full_name']}</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No teachers found</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Submit and Cancel Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" name="add_class" class="btn btn-warning me-2">Add Class</button>
            <a href="index.php?page=class" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
