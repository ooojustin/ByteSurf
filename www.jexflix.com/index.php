<?php
    // index in root directory
    session_start();
    
    if (!isset($_SESSION['username'])) {
        header("location: /login");
        die();
    }
    else if (isset($_SESSION['username'])) {
        header("location: /home");
        die();
    }
    
?>