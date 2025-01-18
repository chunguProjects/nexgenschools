
<div class="container justify-content-center mt-5">
    <h4 class="mb-4">Add New User</h4>
    <form action="../controllers/user-controller.php" method="POST">
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label for="fullName" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullName" name="full_name" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
        </div>
        
    <div class="row mb-3">
    <!-- Country Code Dropdown -->
    <div class="col-md-4">
        <label for="country_code" class="form-label">Country Code</label>
        <select class="form-select" id="country_code" name="country_code" required>
            <option value="" disabled selected>Select Code</option>
            <option value="+265">Malawi (+265)</option>
            <option value="+267">Botswana (+267)</option>
            <option value="+243">DR Congo (+243)</option>
            <option value="+268">Eswatini (+268)</option>
            <option value="+260">Zambia (+260)</option>
            <option value="+263">Zimbabwe (+263)</option>
            <option value="+258">Mozambique (+258)</option>
            <option value="+264">Namibia (+264)</option>
            <option value="+27">South Africa (+27)</option>
            <option value="+266">Lesotho (+266)</option>
        </select>
    </div>

    <!-- Phone Number Field -->
    <div class="col-md-4">
        <label for="phone_number" class="form-label">Phone Number</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number" required>
    </div>
</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="secretary">Secretary</option>
                    <option value="parent">Parent</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
            </div>
        </div>
        
        <div class="d-flex justify-content-start">
            <button type="submit" name="add_user" class="btn btn-warning me-2">Add User</button>
            <a href="index.php?page=user" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
