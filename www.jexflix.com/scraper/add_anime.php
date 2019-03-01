<?php

    require '../inc/server.php';
    
    $post_body = file_get_contents('php://input');
    $data = json_decode($post_body, true);

    // DB Structure
    // title
    // url
    // thumbnail
    // data
    // synonyms

    // Upload structure
    // public class AniDb {
    //    public string name { get; set; }
    //    public string url { get; set; }
    //    public string thumbnail { get; set; }
    //    public string episode_data { get; set; }
    //    public string synonyms { get; set; }
    // }

    $title = $data['name'];
    $url = $data['url'];
    $thumbnail = $data['thumbnail'];
    $ep_data = $data['episode_data'];
    $synonyms = $data['synonyms'];

    $add_anime = $db->prepare('INSERT INTO anime (title, url, thumbnail, data, synonyms) VALUES (:title, :url, :thumbnail, :data, :synonyms);');
    $add_anime->bindValue(':title', $title);
    $add_anime->bindValue(':url', $url);
    $add_anime->bindValue(':thumbnail', $thumbnail);
    $add_anime->bindValue(':data', $ep_data);
    $add_anime->bindValue(':synonyms', $synonyms);

    $add_anime->execute();

?>