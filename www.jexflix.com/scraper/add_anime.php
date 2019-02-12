<?php

    require '../inc/server.php';
    
    $post_body = file_get_contents('php://input');
    $data = json_decode($post_body, true);

    $title = $data['name'];
    $ep_data = $data['episode_data'];

    $get_anime = $db->prepare("SELECT * FROM anime WHERE title=:title");
    $get_anime->bindValue(':title', $title);
    $get_anime->execute();
    if ($get_anime->fetch())
        die();

    $add_anime = $db->prepare('INSERT INTO anime (title, data) VALUES (:title, :data);');
    $add_anime->bindValue(':title', $title);
    $add_anime->bindValue(':data', $ep_data);
    $add_anime->execute();

?>