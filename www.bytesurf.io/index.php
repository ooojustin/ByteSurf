<?php
    // index in root directory
    require 'inc/server.php';
    require 'inc/session.php';
    require_subscription();

    if (!isset($_SESSION['username'])) {
        header("location: /login");
        die();
    }
    else if (isset($_SESSION['username'])) {
        header("location: /home");
        die();
    }

?>
