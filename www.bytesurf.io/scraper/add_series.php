<?php
    require '../inc/server.php';

    $post_body = file_get_contents('php://input');
    $data = json_decode($post_body, true);

    $title = $data['title'];
    $url = $data['url'];
    $description = $data['description'];
    $thumbnail = $data['thumbnail'];
    $preview = $data['preview'];
    $genres = json_encode($data['genres']);
    $imdb_id = $data['imdb_id'];
    $year = $data['year'];
    $certification = $data['certification'];
    $rating = $data['rating'];
    $dataurl = $data['dataurl'];

    $checkExists = $db->prepare('SELECT * FROM series WHERE url=:url');
    $checkExists->bindValue(':url', $url);
    $checkExists->execute();

    if ($checkExists->rowCount() > 0) die();

    $updateTable = $db->prepare("INSERT INTO series (title, url, thumbnail, preview, genres, data, description, year, imdb_id, certification, rating) VALUES (:title, :url, :thumbnail, :preview, :genres, :data, :description, :year, :imdb_id, :certification, :rating);");
    $updateTable->bindValue(':title', $title);
    $updateTable->bindValue(':url', $url);
    $updateTable->bindValue(':thumbnail', $thumbnail);
    $updateTable->bindValue(':preview', $preview);
    $updateTable->bindValue(':genres', $genres);
    $updateTable->bindValue(':data', $dataurl);
    $updateTable->bindValue(':description', $description);
    $updateTable->bindValue(':year', $year);
    $updateTable->bindValue(':imdb_id', $imdb_id);
    $updateTable->bindValue(':certification', $certification);
    $updateTable->bindValue(':rating', $rating);
    $updateTable->execute();

?>
