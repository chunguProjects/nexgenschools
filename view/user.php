<?php
//include model and db file 
include('../config/db.php');
include('../model/user.php');

//create user object

$userModel = new User($conn);

//fetch all users from the database

$users = $userModel->getAllUsers();
?>

<div class="container-fluid py-3">
   <!-- Add User Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>User Management</h4>
    <a href="index.php?page=add-user-form" class="btn btn-warning">
        <i class="fa-solid fa-plus"></i> Add User
    </a>
</div>


    <!-- Users List -->
    <div class="card shadow-sm">
        <div class="card-header">
        <h5 class="card-title mb-0">Users List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //display users from database
                    while ($user = $users->fetch_assoc()) {
                        echo "<tr>
                                <td>{$user['user_id']}</td>
                                <td>{$user['full_name']}</td>
                                <td>{$user['country_code']} {$user['phone_number']}</td>
                                <td>{$user['username']}</td>
                                <td>{$user['email']}</td>
                                <td>{$user['role']}</td>
                                <td>
                                    <button class='btn btn-sm btn-primary editUserBtn' data-id='{$user['user_id']}'>Edit</button>
                                    <button class='btn btn-sm btn-danger deleteUserBtn' data-id='{$user['user_id']}'>Delete</button>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>  
</div>
</div>
</div>
