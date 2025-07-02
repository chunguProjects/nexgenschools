<div class="container justify-content-center mt-5">
    <h4 class="mb-4">Add New Subject</h4>
    <form action="../controllers/subject-controller.php" method="POST">
        <!-- subject Name Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="subject_name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="name" name="subject_name" required>
            </div>
        </div>

        <!-- subject code Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="subject_code" class="form-label">Subject Code</label>
                <input type="text" class="form-control" id="subject_code" name="subject_code" required>
            </div>
        </div>

        <!-- Subject Teacher Field -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="teacher_id" class="form-label">Subject Teacher</label>
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
            <button type="submit" name="add_subject" class="btn btn-warning me-2">Add Subject</button>
            <a href="index.php?page=subject" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
