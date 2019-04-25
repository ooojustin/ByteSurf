<?php
    
    require 'server.php';
    require 'session.php';
    require_subscription();

    /*
        1.) decompress post body (gz)
        2.) decode decompressed data to array (json)
        3.) add results to $_GET (use array_merge func)
        4.) evaluate action & request with $_GET params
        5.) output compressed result (gz)
    */

    $username = $GLOBALS['user']['username'];
    
    if (!isset($_GET['action']))
        die_gz('Action not provided.');

    $payload = file_get_contents('php://input');;
    if (empty($payload))
        die_gz('Reqest payload is empty.');

    $payload_uncompressed = gzuncompress($payload);
    $_GET = array_merge(json_decode($payload_uncompressed, true), $_GET);

    switch ($_GET['action']) {
            
        case 'send_party_chat_message':
            
            // require message parameter
            require_get_params(array('message'));
            
            // send message
            $sent = send_party_chat_message($_GET['message']);
            
            // output whether or not message was sent successfully
            die_gz($sent ? 'true' : 'false');
            
        case 'remove_from_watched':
            
            // require parameters for bind_content_values
            $params = array('s', 'e', 't', 'type');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // delete saved progress data from database
            delete_progress_entry();
            
            // die with new button value & text    
            die_gz('add_to_watched:ADD TO WATCHED');
        
        case 'add_to_watched':
            
            // require parameters for bind_content_values
            $params = array('s', 'e', 't', 'type');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // mark episode as complpeted
            save_progress_entry(0, 0, true);
            
            // die with new button value & text
            die_gz('remove_from_watched:REMOVE FROM WATCHED');
            
        case 'party_update':
            
            // require a myriad of parameters, lol
            $params = array('s', 'e', 't', 'type', 'time', 'timestamp', 'playing');
            require_get_params($params);
            
            // Make sure our timestamp is in sync with the clients (ms, 5 seconds)
            $timestamp_ms = time_ms();
            $request_delta = abs($timestamp_ms - $_GET['timestamp']);
            if ($request_delta > 5000)
                die_gz('Request time delta exceeded limit (5000 ms) = ' . $request_delta);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // get the party, ensure it's valid
            $party = get_active_party();
            if (!$party)
                die_gz('Provided party invalid.');
            
            // update current user information
            $users = empty($party['users']) ? array() : json_decode($party['users'], true);
            $users[$username] = $_GET['timestamp'];
            
            // remove any users that havent updated in > 25 seconds
            foreach ($users as $user => $user_timestamp)
                if (abs($timestamp_ms - $user_timestamp) > 25000)
                    unset($users[$user]);
                
            // send updated user information to database
            update_party_users($users);
            
            $owner = strtolower($username) == strtolower($party['owner']);
            if ($owner) {
                $playing = $_GET['playing'] == 'true';
                update_party($_GET['timestamp'], $_GET['time'], $playing);
            }
            
            // update party information, after queries
            $party = get_party($party['party']);
            
            // return information to client
            $party['owner'] = $owner ? 'true' : 'false';
            $data = json_encode($party);
            die_gz($data);
            
            
        case 'toggle_queued':
            
            // require parameters for title & type
            $params = array('t', 'type');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // determine whether or not it was favorited, and set to opposite
            $queued = is_queued($_GET['type'], $_GET['t']);
            $executed = set_queued($_GET['type'], $_GET['t'], !$queued);
            
            if ($executed)
                die_gz('Queued: ' . strval(!$queued));
            else
                die_gz('Failed to execute query.');
            
        
        case 'save_progress':
            
            // require parameters for title, type, time, and completed
            $params = array('s', 'e', 't', 'type', 'time', 'time_total', 'completed');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // set season and episode to -1 if they're not provided
            default_param('s', -1);
            default_param('e', -1);
            
            // save current information to database
            $completed = $_GET['completed'] === 'true';
            save_progress_entry($_GET['time'], $_GET['time_total'], $completed);
            die_gz('Saved progress successfully: ' . $_GET['time']);
            
        case 'get_progress':
            
            // require parameters for title, type, season, and episode
            $params = array('s', 'e', 't', 'type');
            require_get_params($params);
            
            // make sure type is valid
            validate_type($_GET['type'], true);
            
            // get progress row from database
            $progress = get_progress($username, $_GET['t'], $_GET['type'], $_GET['s'], $_GET['e']);
            
            // return progress (season, episode, time, completed)
            if ($progress)
                die_gz(sprintf('%s,%s', $progress['time'], $progress['completed']));
            else
                die_gz('0,0');
        
    }

?>