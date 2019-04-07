<?php

    require '../inc/server.php';
    
    $get_movies = $db->prepare("SELECT * FROM series WHERE `data` NOT LIKE '%https://cdn.bytesurf.io/shows/%'");
    $get_movies->execute();
    $movies = $get_movies->fetchAll();

    foreach ($movies as $movie) {
       // echo $movie['thumbnail'] . '<br>';
        
        $new_url = "https://cdn.bytesurf.io/shows/" . $movie['url'] . "/data.json";
        echo $new_url . '<br>';
        
        $update = $db->prepare("UPDATE series SET data=:thumbnail WHERE url=:url");
        $update->bindValue(':thumbnail', $new_url);
        $update->bindValue(':url', $movie['url']);
        
        $update->execute();
    }

?>