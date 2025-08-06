<div class="container py-4">
    <h2 class="fw-bold mb-4">Welcome, <?= htmlspecialchars($_SESSION['user']['full_name']) ?> 👋</h2>
    <p class="text-muted">This is your teacher dashboard. Use the sidebar to navigate your classes, enter grades, and manage your work.</p>

    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5>My Classes</h5>
                <p class="text-muted">View all classes you're teaching.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <i class="fas fa-pen-nib fa-3x text-success mb-3"></i>
                <h5>Enter Grades</h5>
                <p class="text-muted">Submit grades for your students.</p>
            </div>
        </div>
    </div>
</div>
