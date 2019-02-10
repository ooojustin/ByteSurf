<?php
    require 'inc/safe_request.php';
    require 'inc/server.php';
    
    $post_body = file_get_contents('php://input');
    $data = json_decode($post_body, true);

    $title = $data['title'];
    $url = $data['url'];
    $description = $data['description'];
    $duration = $data['duration'];
    $thumbnail = $data['thumbnail'];
    $preview = $data['preview'];
    $genres = json_encode($data['genres']);
    $qualities = json_encode($data['qualities']);
    $imdb_id = $data['imdb_id'];
    $year = $data['year'];
    $certification = $data['certification'];

    $updateTable = $db->prepare("INSERT INTO movies (title, url, description, duration, thumbnail, preview, qualities, genres, imdb_id, year, certification) VALUES (:title, :url, :description, :duration, :thumbnail, :preview, :qualities, :genres, :imdb_id, :year, :certification);");
    $updateTable->bindValue(':title', $title);
    $updateTable->bindValue(':url', $url);
    $updateTable->bindValue(':description', $description);
    $updateTable->bindValue(':duration', $duration);
    $updateTable->bindValue(':thumbnail', $thumbnail);
    $updateTable->bindValue(':preview', $preview);
    $updateTable->bindValue(':qualities', $qualities);
    $updateTable->bindValue(':genres', $genres);
    $updateTable->bindValue(':imdb_id', $imdb_id);
    $updateTable->bindValue(':year', $year);
    $updateTable->bindValue(':certification', $certification);
    $updateTable->execute();

?>