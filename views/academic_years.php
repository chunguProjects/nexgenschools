<?php
include('../config/db.php');
include('../model/academic_year.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ayModel = new AcademicYear($conn);
$result  = $ayModel->getAll(); // This returns an ARRAY
?>

<!DOCTYPE html>
<html>
<head>
    <title>Academic Years</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container-fluid py-3">

    <div class="d-flex align-items-center mb-3">

        <!-- Left -->
        <div style="width: 120px;">
            <a href="admin.php?page=dashboard" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Center -->
        <div class="flex-grow-1 text-center">
            <h4 class="mb-0">Academic Years</h4>
        </div>

        <!-- Right (balance space) -->
        <div style="width: 120px;"></div>

    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form id="academicYearForm" 
          action="../controllers/academic_year_controller.php" 
          method="post" 
          class="mb-4">

        <input type="hidden" name="academic_year_id" id="academic_year_id" value="">

        <div class="row g-2">
            <div class="col-md-2">
                <input type="text" 
                       name="name" 
                       id="name" 
                       placeholder="2025-2026" 
                       class="form-control" 
                       required>
            </div>

            <div class="col-md-3">
                <input type="date" 
                       name="start_date" 
                       id="start_date" 
                       class="form-control" 
                       required>
            </div>

            <div class="col-md-3">
                <input type="date" 
                       name="end_date" 
                       id="end_date" 
                       class="form-control" 
                       required>
            </div>

            <div class="col-md-2 d-flex align-items-center">
                <input type="checkbox" 
                       name="is_current" 
                       id="is_current" 
                       value="1" 
                       class="form-check-input me-2">
                <label class="form-check-label">Current?</label>
            </div>

            <div class="col-md-2">
                <button type="submit" 
                        id="formSubmitBtn" 
                        class="btn btn-primary w-100">
                    Save
                </button>
            </div>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered table-hover" id="academicYearTable">
        <thead class="table-light">
            <tr>
                <th>Year</th>
                <th>Start</th>
                <th>End</th>
                <th>Current?</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($result)): ?>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td class="year_name"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="year_start"><?= htmlspecialchars($row['start_date']) ?></td>
                        <td class="year_end"><?= htmlspecialchars($row['end_date']) ?></td>
                        <td class="year_current text-center">
                            <?= $row['is_current'] ? '✅' : '' ?>
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-warning btn-sm editBtn"
                                    data-id="<?= $row['academic_year_id'] ?>"
                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                    data-start="<?= $row['start_date'] ?>"
                                    data-end="<?= $row['end_date'] ?>"
                                    data-current="<?= $row['is_current'] ?>">
                                Edit
                            </button>

                            <!-- <a href="../controllers/academic_year_controller.php?delete=<?= $row['academic_year_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this academic year?')">
                               Delete
                            </a> -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No academic years found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<script>
    // Handle Edit button clicks
    const editButtons = document.querySelectorAll('.editBtn');
    const formId = document.getElementById('academic_year_id');
    const nameInput = document.getElementById('name');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const currentInput = document.getElementById('is_current');
    const submitBtn = document.getElementById('formSubmitBtn');
    const form = document.getElementById('academicYearForm');

    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            formId.value = btn.dataset.id;
            nameInput.value = btn.dataset.name;
            startInput.value = btn.dataset.start;
            endInput.value = btn.dataset.end;
            currentInput.checked = btn.dataset.current == "1";
            submitBtn.textContent = "Update";
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Reset form after submission
    form.addEventListener('submit', () => {
        setTimeout(() => {
            formId.value = '';
            nameInput.value = '';
            startInput.value = '';
            endInput.value = '';
            currentInput.checked = false;
            submitBtn.textContent = 'Save';
        }, 100);
    });

    // Auto-hide alerts after 3 seconds
    window.addEventListener('DOMContentLoaded', () => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        });
    });
</script>

</body>
</html>