<?php
    session_start();

    // 1. Unset all of the session variables
    $_SESSION = array();

    // 2. Destroy the session
    session_destroy();

    // 3. Redirect to the homepage
    header("Location: index.php");
    exit;
?>
