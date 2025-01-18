<?php
include_once('../model/class.php');
include_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $class = new ClassGroup($conn);
    $result = $class->addClass($_POST['name'], $_POST['description'], $_POST['teacher_id']);
    
    if ($result) {
        // Redirect with success message
        header("Location: ../view/add-class-form.php?message=Class+Added+Successfully");
        exit;
    } else {
        // Handle error
        header("Location: ../view/add-class-form.php?error=Error+Adding+Class");
        exit;
    }
    
}
?>
