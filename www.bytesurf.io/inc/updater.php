<?php
    
    require 'server.php';
    require 'session.php';
    require_subscription();

    $username = $GLOBALS['user']['username'];
    
    if (!isset($_GET['action']))
        die('Action not provided.');

    switch ($_GET['action']) {
            
        case 'toggle_favorite':
            
            // require parameters for title & type
            $params = array('t', 'type');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type']);
            
            // determine whether or not it was favorited, and set to opposite
            $favorited = is_favorited($_GET['type'], $_GET['t']);
            $executed = set_favorited($_GET['type'], $_GET['t'], !$favorited);
            
            if ($executed)
                die('Favorited: ' . strval(!$favorited));
            else
                die('Failed to execute query.');
            
        
        case 'save_progress':
            
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
            
        case 'get_progress':
            
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
        bind_content_values($get_progress, $type, $title, $season, $episode);
        $get_progress->bindValue(':username', $username);
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
        bind_content_values($save_progress, $type, $title, $season, $episode);
        $save_progress->bindValue(':username', $username);
        $save_progress->bindValue(':time', $time);
        $save_progress->bindValue(':completed', $completed);
        $save_progress->execute();
    }

    function bind_content_values($query, $type, $title, $season = -1, $episode = -1) {
        $query->bindValue(':type', $type);
        $query->bindValue(':title', $title);
        $query->bindValue(':season', $season);
        $query->bindValue(':episode', $episode);
    }

?>