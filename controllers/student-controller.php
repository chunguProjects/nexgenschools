
<?php
// Include database connection and Student model
include('../config/db.php');
include('../model/student.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get class_id from the form
    $classId = $_POST['class_id'];

    // Create an array of students
    $students = [];
    foreach ($_POST['first_name'] as $index => $firstName) {
        $students[] = [
            'first_name' => $firstName,
            'last_name' => $_POST['last_name'][$index],
            'gender' => $_POST['gender'][$index]
        ];
    }

    // Create Student model object
    $studentModel = new Student($conn);

    // Add students to the class
    if ($studentModel->addStudentsToClass($students, $classId)) {
        header('Location: ../view/class.php?success=1'); // Redirect on success
        exit();
    } else {
        header('Location: ../view/add-students-form.php?class_id=' . $classId . '&error=1'); // Redirect on failure
        exit();
    }
}
?>
