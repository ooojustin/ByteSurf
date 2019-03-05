<?php

    require 'inc/server.php';
    
    if (!isset($_GET['user'])) die();
    if (!isset($_GET['amount'])) die();
    
    for ($x = 0; $x <= $_GET['amount']; $x++) {
        generate_trial_key($_GET['user'], 604800);
    }

?>