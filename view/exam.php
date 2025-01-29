<?php
// Include necessary files
include('../config/db.php');
include('../model/exam.php');

// Create an instance of the Exams class
$examModel = new Exams($conn);

// Retrieve all exams
$exams = $examModel->getAllExams();
?>

<div class="container-fluid py-3">
    <!-- Add Exam Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Exam Management</h4>
        <a href="index.php?page=add-exam-form" class="btn btn-warning">
            <i class="fa-solid fa-plus"></i> Add Exam
        </a>
    </div>

    <!-- Exams List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Exam List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Exam Name</th>
                        <th>Term</th>
                        <th>Academic Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($exams)) : ?>
                        <?php foreach ($exams as $exam) : ?>
                            <tr>
                                <td><?php echo $exam['id']; ?></td>
                                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                <td><?php echo htmlspecialchars($exam['term']); ?></td>
                                <td><?php echo htmlspecialchars($exam['academic_year']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary editExamBtn" data-id="<?php echo $exam['id']; ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteExamBtn" data-id="<?php echo $exam['id']; ?>">Delete</button>
                                    <a href="index.php?page=add-grades-form&exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-success">Add Grades</a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center">No Exams Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
