<?php
include('../config/db.php');
include('../model/academic_year.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ayModel = new AcademicYear($conn);
$result  = $ayModel->getAll();
?>


<div class="container-fluid">


    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">

        <div>
            <a href="admin.php?page=dashboard" 
               class="btn btn-ledger-outline btn-sm">

                <i class="fa fa-arrow-left me-1"></i>
                Back

            </a>
        </div>


        <div class="flex-grow-1 text-center">

            <h3 class="mb-0 page-title">
                Academic Years
            </h3>

        </div>


        <div style="width:80px;"></div>


    </div>



    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success ledger-alert">

            <?= $_SESSION['success']; ?>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger ledger-alert">

            <?= $_SESSION['error']; ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>





    <!-- Add / Edit Form -->

    <div class="card mb-4">

        <div class="card-body">


            <form id="academicYearForm"
                  action="../controllers/academic_year_controller.php"
                  method="post">


                <input type="hidden"
                       name="academic_year_id"
                       id="academic_year_id">


                <div class="row g-3">


                    <div class="col-md-2">

                        <label class="form-label">
                            Academic Year
                        </label>

                        <input type="text"
                               name="name"
                               id="name"
                               placeholder="2026-2027"
                               class="form-control"
                               required>

                    </div>



                    <div class="col-md-3">

                        <label class="form-label">
                            Start Date
                        </label>

                        <input type="date"
                               name="start_date"
                               id="start_date"
                               class="form-control"
                               required>

                    </div>



                    <div class="col-md-3">

                        <label class="form-label">
                            End Date
                        </label>

                        <input type="date"
                               name="end_date"
                               id="end_date"
                               class="form-control"
                               required>

                    </div>



                    <div class="col-md-2 d-flex align-items-end">

                        <div class="form-check mb-2">

                            <input type="checkbox"
                                   name="is_current"
                                   id="is_current"
                                   value="1"
                                   class="form-check-input">


                            <label class="form-check-label">
                                Current Year
                            </label>

                        </div>

                    </div>




                    <div class="col-md-2 d-flex align-items-end">


                        <button type="submit"
                                id="formSubmitBtn"
                                class="btn btn-ledger-solid w-100">

                            Save

                        </button>


                    </div>



                </div>


            </form>


        </div>

    </div>






    <!-- Table -->

    <div class="card">


        <div class="card-body p-0">


            <div class="table-responsive">


                <table class="table ledger-table mb-0"
                       id="academicYearTable">


                    <thead>

                        <tr>

                            <th>
                                Year
                            </th>


                            <th>
                                Start
                            </th>


                            <th>
                                End
                            </th>


                            <th class="text-center">
                                Current
                            </th>


                            <th>
                                Actions
                            </th>


                        </tr>

                    </thead>



                    <tbody>


                    <?php if (!empty($result)): ?>


                        <?php foreach ($result as $row): ?>


                            <tr>


                                <td>
                                    <?= htmlspecialchars($row['name']) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars($row['start_date']) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars($row['end_date']) ?>
                                </td>


                                <td class="text-center">

                                    <?php if($row['is_current']): ?>

                                        <span class="badge bg-success">
                                            Current
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>


                                    <button type="button"
                                            class="btn btn-ledger-outline btn-sm editBtn"

                                            data-id="<?= $row['academic_year_id'] ?>"

                                            data-name="<?= htmlspecialchars($row['name']) ?>"

                                            data-start="<?= $row['start_date'] ?>"

                                            data-end="<?= $row['end_date'] ?>"

                                            data-current="<?= $row['is_current'] ?>">


                                        <i class="fa fa-edit"></i>
                                        Edit


                                    </button>



                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td colspan="5"
                                class="text-center text-muted py-4">

                                No academic years found.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>


    </div>


</div>





<script>

const editButtons = document.querySelectorAll('.editBtn');

const formId = document.getElementById('academic_year_id');
const nameInput = document.getElementById('name');
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');
const currentInput = document.getElementById('is_current');
const submitBtn = document.getElementById('formSubmitBtn');


editButtons.forEach(btn => {

    btn.addEventListener('click',()=>{


        formId.value = btn.dataset.id;

        nameInput.value = btn.dataset.name;

        startInput.value = btn.dataset.start;

        endInput.value = btn.dataset.end;

        currentInput.checked = btn.dataset.current == "1";


        submitBtn.textContent = "Update";


        window.scrollTo({

            top:0,
            behavior:'smooth'

        });


    });


});



setTimeout(()=>{

    document.querySelectorAll('.alert').forEach(a=>{

        a.style.opacity='0';

        setTimeout(()=>a.remove(),500);

    });


},3000);


</script>