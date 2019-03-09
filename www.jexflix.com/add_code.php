<?php

    require 'inc/server.php';
    define('SECONDS_PER_DAY', 86400);
    
    if (!isset($_GET['user'])) 
    	die('Please provide \'user\' variable in URL.');

    $amount = 1; // number of keys
    if (isset($_GET['amount']))
    	$amount = intval($_GET['amount']);

    $duration = 7; // in days
    if (isset($_GET['duration']))
    	$duration = intval($_GET['duration']);

    // keep it lifetime if it's set to -1
    if ($duration > -1)
        $duration *= SECONDS_PER_DAY; // turn into days
    
    for ($i = 0; $i < $amount; $i++)
        generate_trial_key($_GET['user'], $duration);

?>