<?php
session_start();
session_destroy();
header('Location: ../views/student-portal.php');
exit;
