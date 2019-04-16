<?php
    
    require 'server.php';
    require 'session.php';
    require_subscription();

    $username = $GLOBALS['user']['username'];
    
    if (!isset($_GET['action']))
        die('Action not provided.');

    switch ($_GET['action']) {
            
        case 'party_update':
            
            // require a myriad of parameters, lol
            $params = array('party', 's', 'e', 't', 'type', 'time', 'timestamp', 'playing');
            require_get_params($params);
            
            // Make sure our timestamp is in sync with the clients (ms, 5 seconds)
            $timestamp_ms = round(microtime(true) * 1000);
            $request_delta = abs($timestamp_ms - $_GET['timestamp']);
            if ($request_delta > 5000)
                die('Request time delta exceeded limit (5000 ms) = ' . $request_delta);
            
            // make sure type is valid
            validate_type($_GET['type']);
            
            // get the party, ensure it's valid
            $party = get_party($_GET['party']);
            if (!$party)
                die('Provided party invalid.');
            
            // update current user information
            $users = empty($party['users']) ? array() : json_decode($party['users'], true);
            $users[$username] = $_GET['timestamp'];
            
            // remove any users that havent updated in > 25 seconds
            foreach ($users as $user => $user_timestamp)
                if (abs($timestamp_ms - $user_timestamp) > 25000)
                    unset($users[$user]);
                
            // send updated user information to database
            update_party_users($_GET['party'], $users);
            
            $owner = strtolower($username) == strtolower($party['owner']);
            if ($owner) {
                $playing = $_GET['playing'] == 'true';
                update_party($_GET['party'], $_GET['timestamp'], $_GET['time'], $playing);
            }
            
            // update party information, after queries
            $party = get_party($_GET['party']);
            
            // return information to client
            $party['owner'] = $owner ? 'true' : 'false';
            $data = json_encode($party);
            die($data);
            
            
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
            $params = array('s', 'e', 't', 'type', 'time', 'completed');
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
            $params = array('s', 'e', 't', 'type');
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

    function update_party_users($party, $users) {
        global $db;
        $update_party = $db->prepare('UPDATE parties SET users=:users WHERE party=:party');
        $update_party->bindValue(':users', json_encode($users));
        $update_party->bindValue(':party', $party);
        return $update_party->execute();
    }

    function update_party($party, $timestamp, $time, $playing) {
        global $db;
        $update_party = $db->prepare('UPDATE parties SET type=:type, title=:title, season=:season, episode=:episode, timestamp=:timestamp, time=:time, playing=:playing WHERE party=:party');
        bind_content_values($update_party);
        $update_party->bindValue(':party', $party);
        $update_party->bindValue(':timestamp', $timestamp);
        $update_party->bindValue(':time', $time);
        $update_party->bindValue(':playing', $playing);
        return $update_party->execute();
    }
    
    function get_progress($username) {
        global $db;
        $get_progress = $db->prepare('SELECT * FROM progress_tracker WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode ORDER BY id DESC LIMIT 1');
        bind_content_values($get_progress);
        $get_progress->bindValue(':username', $username);
        $get_progress->execute();
        return $get_progress->fetch();
    }

    function save_progress($username, $time, $completed) {
        global $db;
        if (get_progress($username))
            $query = 'UPDATE progress_tracker SET time=:time, completed=:completed WHERE username=:username AND title=:title AND type=:type AND season=:season AND episode=:episode';
        else
            $query = 'INSERT INTO progress_tracker (username, type, title, season, episode, time, completed) VALUES (:username, :type, :title, :season, :episode, :time, :completed)';
        $save_progress = $db->prepare($query);
        bind_content_values($save_progress);
        $save_progress->bindValue(':username', $username);
        $save_progress->bindValue(':time', $time);
        $save_progress->bindValue(':completed', $completed);
        return $save_progress->execute();
    }

    // automatically binds type/s/e/t to a given statement (query object)
    function bind_content_values($query) {
        $query->bindValue(':type', $_GET['type']);
        $query->bindValue(':title', $_GET['t']);
        $query->bindValue(':season', $_GET['s']);
        $query->bindValue(':episode', $_GET['e']);
    }

?>