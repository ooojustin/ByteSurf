<?php

    require '../inc/server.php';
    
    $rating = $_POST['rating'];
    $url = $_POST['url'];

    $updateTable = $db->prepare("UPDATE movies SET rating=:rating WHERE url=:url");
    $updateTable->bindValue(':rating', $rating);
    $updateTable->bindValue(':url', $url);
    $updateTable->execute();

?>