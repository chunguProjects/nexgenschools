<?php
// Teachers use the same Profile & Settings page as admins —
// it already adapts its content (e.g. the "My Assignments" card)
// based on $_SESSION['user']['role_name'].
require __DIR__ . '/../profile.php';