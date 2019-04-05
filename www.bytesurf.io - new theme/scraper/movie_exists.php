<?php

    require '../inc/safe_request.php';
    require '../inc/server.php';
    global $db, $sr;

    if ($_SERVER['HTTP_USER_AGENT'] != 'jexflix-client')
        $sr->output(false, 'invalid request agent.');

    $data['exists'] = boolval(videoLinkExists($_POST['url'])); 
    $sr->output(true, 'evaluated successfully.', $data);

    function videoLinkExists($url) {
        global $db;
        $get_video = $db->prepare('SELECT * FROM movies WHERE url=:url');
        $get_video->bindValue(':url', $_POST['url']);
        $get_video->execute();
        return $get_video->fetch();
    }

?>

