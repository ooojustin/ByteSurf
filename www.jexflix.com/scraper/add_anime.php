<?php
    require 'inc/safe_request.php';
    require 'inc/server.php';

    define('ENCRYPTION_KEY', 'jexflix');
    $sr = new SafeRequest(ENCRYPTION_KEY);
    
    $post_body = file_get_contents('php://input');
    $data = json_decode($post_body, true);

    $title = $data['name'];
    $ep_data = $data['episode_data'];

    $checkAnime = $db->prepare("SELECT * FROM anime WHERE title=:title");
    $checkAnime->bindValue(':title', $title);
    $checkAnime->execute();
    if ($checkAnime->rowCount() > 0) {
        die();
    }

    $updateTable = $db->prepare('INSERT INTO anime (title, data) VALUES (:title, :data);');
    $updateTable->bindValue(':title', $title);
    $updateTable->bindValue(':data', $ep_data);

    $updateTable->execute();

?>