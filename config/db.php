<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nextgen";

// create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

if (!defined('BASE_URL')) {
    // config/db.php lives at <project-root>/config/db.php,
    // so the project root is one level up from this file.
    $projectRoot = dirname(__DIR__);

    // Normalize slashes (Windows uses backslashes) and strip the
    // document root prefix, leaving just the app's sub-path (if any).
    $documentRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $normalizedRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');

    $basePath = str_replace($documentRoot, '', $normalizedRoot);
    $basePath = '/' . trim($basePath, '/'); // ensures exactly one leading slash

    // If the app sits at the domain root, $basePath becomes "/" —
    // normalize that to "" so BASE_URL . '/uploads/x.jpg' doesn't
    // produce a double slash.
    if ($basePath === '/') {
        $basePath = '';
    }

    define('BASE_URL', $basePath);
}