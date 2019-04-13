<?php
    
    require 'server.php';
    require 'session.php';
    require_subscription();

    $username = $GLOBALS['user']['username'];
    
    if (!isset($_GET['action']))
        die('Action not provided.');

    switch ($_GET['action']) {
        
        case 'save':
            
            // require parameters for title, type, time, and completed
            $params = array('t', 'type', 'time', 'completed');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type']);
            
            // set season and episode to -1 if they're not provided
            default_get_param('s', -1);
            default_get_param('e', -1);
            
            // save current information to database
            $completed = $_GET['completed'] === 'true';
            save_progress($username, $_GET['t'], $_GET['type'], $_GET['time'], $completed, $_GET['s'], $_GET['e']);
            die('Saved progress successfully: ' . $_GET['time']);
            
        case 'get':
            
            // require parameters for title, type, season, and episode
            $params = array('t', 'type', 's', 'e');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type']);
            
            // get progress row from database
            $progress = get_progress($username, $_GET['t'], $_GET['type'], $_GET['s'], $_GET['e']);
            
            // return progress (season, episode, time, completed)
            if ($progress)
                die(sprintf('%s,%s', $progress['time'], $progress['completed']));
            else
                die('0,0');
        
    }

    function validate_type($type) {
        $types = array('movie', 'show', 'anime');
        if (!in_array($type, $types))
            die('Invalid type provided: ' . $type);
    }

    function require_get_params($params) {
        foreach ($params as $param)
            if (!isset($_GET[$param]))
                die('Missing provided parameter: ' . $param);
    }

    function default_get_param($param, $value) {
        if (!isset($_GET[$param]))
            $_GET[$param] = $value;
    }
    
    function get_progress($username, $title, $type, $season = -1, $episode = -1) {
        global $db;
        $get_progress = $db->prepare('SELECT * FROM progress_tracker WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode ORDER BY id DESC LIMIT 1');
        $get_progress->bindValue(':username', $username);
        $get_progress->bindValue(':title', $title);
        $get_progress->bindValue(':type', $type);
        $get_progress->bindValue(':season', $season);
        $get_progress->bindValue(':episode', $episode);
        $get_progress->execute();
        return $get_progress->fetch();
    }

    function save_progress($username, $title, $type, $time, $completed, $season = -1, $episode = -1) {
        global $db;
        if (get_progress($username, $title, $type, $season, $episode))
            $query = 'UPDATE progress_tracker SET time=:time, completed=:completed WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode';
        else
            $query = 'INSERT INTO progress_tracker (username, type, title, season, episode, time, completed) VALUES (:username, :type, :title, :season, :episode, :time, :completed)';
        $save_progress = $db->prepare($query);
        $save_progress->bindValue(':username', $username);
        $save_progress->bindValue(':title', $title);
        $save_progress->bindValue(':type', $type);
        $save_progress->bindValue(':time', $time);
        $save_progress->bindValue(':completed', $completed);
        $save_progress->bindValue(':season', $season);
        $save_progress->bindValue(':episode', $episode);
        $save_progress->execute();
    }

?>