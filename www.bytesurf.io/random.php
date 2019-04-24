<?php

    require 'inc/server.php';
    
    // generate random id
    $get_count = $db->prepare('SELECT * FROM movies');
    $get_count->execute();
    $count = $get_count->rowCount();
    $id = rand(1, $count);
    
    // get movie url
    $get_movie = $db->prepare('SELECT * FROM movies WHERE id=:id');
    $get_movie->bindValue(':id', $id);
    $get_movie->execute();
    $movie = $get_movie->fetch()['url'];

    // redirect to movie
    header("location: https://bytesurf.io/movie.php?t=$movie");

?>