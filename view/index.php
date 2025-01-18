<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextGen Schools</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php
        session_start();
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        include('sidebar.php');
        ?>
        
        <div class="main">
            <!-- Header -->
            <?php include('header.php'); ?>

            <!-- Main Content -->
            <main class="content">
                <?php
                // Dynamically include the corresponding page
                $file = $currentPage . ".php";
                if (file_exists($file)) {
                    include($file);
                } else {
                    echo "<div class='alert alert-danger mt-3'>Page not found</div>";
                }
                ?>

                 
           
            </main>
            <!-- Footer -->
            <?php include('footer.php'); ?>

        </div>
        
    </div>

    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>

</html>
