<?php
    
    require 'bunnycdn.php';

    define('BUNNYCDN_API_KEY', '980938c9-68a9-47ed-8d4b-ea0f99892a75cea8726f-cbed-4272-a173-bb94d80044b9');
    $bcdn = new BunnyCDN(BUNNYCDN_API_KEY, 'ByteSurf.io Server');

    echo json_encode($bcdn->get_billing_summary());

?>