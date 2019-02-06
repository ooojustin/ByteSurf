<?php

    $server = "localhost";
    $dbname = "jexflixc_db";
    $username = "jexflixc_admin";
    $password = "K+VLZP;x{G%Q";

    $db = new PDO('mysql:host=' . $server . ';dbname=' . $dbname, $username, $password,[PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

?>