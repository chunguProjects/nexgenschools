<?php
/**
 * session-check.php
 *
 * Include this at the very top of every protected page/controller
 * (after session_start()). Enforces a sliding inactivity timeout:
 * if more than SESSION_TIMEOUT seconds have passed since the user's
 * last recorded activity, the session is destroyed and they're
 * bounced to login. Otherwise, this call itself counts as activity,
 * so the timer resets on every page load/AJAX request that includes
 * this file.
 */

const SESSION_TIMEOUT = 600; // 10 minutes, in seconds

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Not logged in at all — nothing to time out, calling page should
// handle its own "please log in" redirect.
if (!isset($_SESSION['user'])) {
    return;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    // Timed out — clear everything and force a fresh login.
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    // Relative to the file that included session-check.php (dashboard/
    // admin.php, teacher.php, student.php all live one level below the
    // project root, same as views/ — so "../views/..." resolves correctly
    // regardless of whether the whole project sits at the domain root
    // (InfinityFree) or inside a subfolder (local XAMPP/WAMP).
    $loginPath = strpos($_SERVER['REQUEST_URI'], 'student.php') !== false
        ? '../views/student-portal.php'
        : '../views/login.php';

    header('Location: ' . $loginPath . '?timeout=1');
    exit;
}

// Still within the timeout window — refresh the activity timestamp.
$_SESSION['last_activity'] = time();