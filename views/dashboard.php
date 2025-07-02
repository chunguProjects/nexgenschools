<main class="content px-3 py-2">
  <div class="container-fluid">
    <!-- Stats Row -->
    <div class="row mb-4">
      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h5>Total Students</h5>
            <h2>1,230</h2>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h5>Total Teachers</h5>
            <h2>75</h2>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h5>Total Classes</h5>
            <h2>32</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <!-- Upcoming Exams -->
      <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0">Upcoming Exams</h5>
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              Math Midterm - 2025-07-05
            </li>
            <li class="list-group-item">
              Science Quiz - 2025-07-12
            </li>
            <li class="list-group-item">
              English Final - 2025-07-20
            </li>
          </ul>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0">Quick Actions</h5>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <a href="/add-class" class="btn btn-primary btn-sm">Add Class</a>
            <a href="/add-subject" class="btn btn-primary btn-sm">Add Subject</a>
            <a href="/add-exam" class="btn btn-primary btn-sm">Add Exam</a>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0">Notifications</h5>
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item text-warning">
              3 students unassigned to classes
            </li>
            <li class="list-group-item text-info">
              1 new teacher registration pending approval
            </li>
            <li class="list-group-item text-danger">
              2 overdue exams results to submit
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Calendar Preview -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0">Calendar Preview</h5>
          </div>
          <div class="card-body">
            <!-- Placeholder for calendar widget -->
            <div id="calendar" style="height: 300px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #999;">
              Calendar Preview (exams, holidays)
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
