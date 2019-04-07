<?php

    require '../inc/server.php';
    
    $get_movies = $db->prepare("SELECT * FROM movies WHERE thumbnail NOT LIKE '%https://cdn.bytesurf.io/movies/%'");
    $get_movies->execute();
    $movies = $get_movies->fetchAll();

    foreach ($movies as $movie) {
       // echo $movie['thumbnail'] . '<br>';
        
        $new_url = "https://cdn.bytesurf.io/movies/" . $movie['url'] . "/thumbnail.jpg";
        echo $new_url . '<br>';
        
        $update = $db->prepare("UPDATE movies SET thumbnail=:thumbnail WHERE url=:url");
        $update->bindValue(':thumbnail', $new_url);
        $update->bindValue(':url', $movie['url']);
        
        $update->execute();
    }

?>