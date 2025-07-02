<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');

$schoolId = $_SESSION['user']['school_id'] ?? null;
?>

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
                    if ($schoolId) {
                        // Step 1: Get role_id for 'teacher'
                        $roleStmt = $conn->prepare("SELECT role_id FROM roles WHERE name = ?");
                        $roleName = 'teacher';
                        $roleStmt->bind_param("s", $roleName);
                        $roleStmt->execute();
                        $roleResult = $roleStmt->get_result();

                        if ($roleRow = $roleResult->fetch_assoc()) {
                            $teacherRoleId = $roleRow['role_id'];

                            // Step 2: Get teachers with that role_id and same school_id
                            $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE role_id = ? AND school_id = ?");
                            $stmt->bind_param("ii", $teacherRoleId, $schoolId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($teacher = $result->fetch_assoc()) {
                                    echo "<option value='{$teacher['user_id']}'>{$teacher['full_name']}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No teachers found</option>";
                            }
                        } else {
                            echo "<option value='' disabled>Teacher role not found</option>";
                        }
                    } else {
                        echo "<option value='' disabled>School not identified</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Submit and Cancel Buttons -->
        <div class="d-flex justify-content-start">
            <button type="submit" name="add_class" class="btn btn-warning me-2">Add Class</button>
            <a href="schooladmin.php?page=class" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
