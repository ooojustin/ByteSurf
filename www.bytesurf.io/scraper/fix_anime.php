<?php

    $json = $_GET['json'];

    $json_decoded = json_decode($json, true);

    foreach($json_decoded as $item) {
        echo $item . "</br>";
    }

?>