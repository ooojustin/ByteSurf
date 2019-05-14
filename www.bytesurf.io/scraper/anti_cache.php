<?php

    // This is a php file scrapers make a request to in
    // order to upload their last episode for this series etc
    // and not be cached up in bunnycdn

    // Usage Instructions
    // Send a GET requests to scraper/anti_cache.php
    // With the url to purge as p
    // The url to purge should be in the frmat of jexflix.b-cdn.net / etc
    // There is no need for the authentication of the url

    require '../inc/bunnycdn/bunnycdn.php';

    define('BUNNYCDN_USER_AGENT', 'ByteSurf.io Server');
    define('BUNNYCDN_API_KEY', '980938c9-68a9-47ed-8d4b-ea0f99892a75cea8726f-cbed-4272-a173-bb94d80044b9');

    $bcdn = new BunnyCDN(BUNNYCDN_API_KEY, BUNNYCDN_USER_AGENT);

    $url_to_purge = str_replace('jexflix.b-cdn.net', 'cdn.bytesurf.io',  $_GET['p']);

    if (strpos($url_to_purge, 'jexflix.b-cdn.net') !== false) {
        // calling this function crashes
        $bcdn->purge_cache($url_to_purge);
        die("Success");
    } else {
        die("Invalid URL Format");
    }

?>
