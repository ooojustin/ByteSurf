<?php

    require '../inc/server.php';
    
    $post_body = file_get_contents('php://input');
    $data = json_decode(utf8_encode($post_body), true);
    $title = $data['name'];
    $url = $data['url'];
    $thumbnail = $data['thumbnail'];
    $ep_data = $data['episode_data'];
    $similar = $data['similar'];
    $genres = $data['genres'];
    $ratings = $data['rating'];
    $release = $data['release'];
    $duration = $data['duration'];
    $age_class = $data['age_class'];
    $cover = $data['cover'];

    // Check for existing so we dont have extras
    $get_anime = $db->prepare("SELECT * FROM anime WHERE url=:url");
    $get_anime->bindValue(':url', $url);
    $get_anime->execute();
    $get_anime->fetch();
        
    if ($get_anime->rowCount() > 0)
        die();
        											
    $add_anime = $db->prepare('INSERT INTO anime (title, url, thumbnail, data, similar, genres, rating, release_date, duration, ageclass, cover) VALUES (:title, :url, :thumbnail, :data, :similar, :genres, :rating, :release_date, :duration, :ageclass, :cover);');
    $add_anime->bindValue(':title', $title);
    $add_anime->bindValue(':url', $url);
    $add_anime->bindValue(':thumbnail', $thumbnail);
    $add_anime->bindValue(':data', $ep_data);
    $add_anime->bindValue(':similar', $similar);
    $add_anime->bindValue(':genres', $genres);
    $add_anime->bindValue(':rating', $ratings);
    $add_anime->bindValue(':release_date', $release);
    $add_anime->bindValue(':duration', $duration);
    $add_anime->bindValue(':ageclass', $age_class);
    $add_anime->bindValue(':cover', $cover);

    $add_anime->execute();

?>