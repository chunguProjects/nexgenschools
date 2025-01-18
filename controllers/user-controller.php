<?php
// controller/user-controller.php
include_once('../model/user.php');
include_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $user = new User($conn);
    $result = $user->addUser($_POST['full_name'], $_POST['country_code'], $_POST['phone_number'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['role']);
    
    if ($result) {
        // Redirect with success message
        header("Location: ../view/add-user-form.php?message=User+Added+Successfully");
        exit;
    } else {
        // Handle error
        header("Location: ../view/add-user-form.php?error=Error+Adding+User");
        exit;
    }
    
}

?>
