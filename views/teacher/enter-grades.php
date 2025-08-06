<div class="container py-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-pen-nib me-2 text-success"></i>Enter Grades</h4>
    <p class="text-muted">Record grades for your subjects in assigned classes.</p>

    <!-- Form Placeholder -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" id="class" disabled>
                            <option>Standard 5</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subject" class="form-label">Subject</label>
                        <select class="form-select" id="subject" disabled>
                            <option>Mathematics</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="exam" class="form-label">Exam</label>
                        <select class="form-select" id="exam" disabled>
                            <option>Term 2 - 2025</option>
                        </select>
                    </div>
                </div>

                <!-- Student Grades Table Placeholder -->
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Banda</td>
                            <td><input type="number" class="form-control form-control-sm" min="0" max="100" /></td>
                        </tr>
                        <tr>
                            <td>Mary Phiri</td>
                            <td><input type="number" class="form-control form-control-sm" min="0" max="100" /></td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-success mt-3">Submit Grades</button>
            </form>
        </div>
    </div>
</div>
