<?php

    require 'inc/server.php';
    
    $get_count = $db->prepare('SELECT * FROM movies');
    $get_count->execute();
    $count = $get_count->rowCount();
    
    $get_movie = $db->prepare('SELECT * FROM movies WHERE id=:id');
    $get_movie->bindValue(':id', rand(1, $count));
    $get_movie->execute();
    $movie = $get_movie->fetch()['url'];
    
    header("location: https://jexflix.com/movie.php?t=$movie");

?>