<?php
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy the session cookie (cleans up the browser side)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session storage (cleans up the server side)
session_destroy();

// 4. Redirect to home
header("Location: index.html");
exit;
?>