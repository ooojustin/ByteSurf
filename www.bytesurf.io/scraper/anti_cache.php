<?php

    // This is a php file scrapers make a request to in
    // order to upload their last episode for this series etc 
    // and not be cached up in bunnycdn

    require '../inc/server.php';
    require '../inc/bunnycdn/bunnycdn.php';

    define('BUNNYCDN_USER_AGENT', 'ByteSurf.io Server');
    $bcdn = new BunnyCDN($_GET['key'], BUNNYCDN_USER_AGENT);
    

?>