<?php

	include 'inc/server.php';
	include 'inc/session.php';
	// require_subscription();

	if (!isset($_GET['action']))
        _die('failed', 'action not specified');

    switch ($_GET['action']) {
        case 'favorite':
            require_get(array('type', 'url', 'v'));
            require_login();
            global $user;
            $is_favorited = intval($_GET['v']) > 0;
            $status = set_favorited($user['username'], $_GET['type'], $_GET['url'], $is_favorited);
            _die($status ? 'completed' : 'failed', 'attempted to update favorites');
    }

    // require $_GET params, exit script if they're not provided
    // pass an array of strings to this func
    function require_get($vars) {
        foreach ($vars as $var)
            if (!isset($_GET[$var]))
                _die('failed', 'missing required param: $_GET[\'' . $var . '\']');
    }

    // outputs json encoded status and message to be interpreted by javascript
    function _die($status, $message) {
        $data = array(
            'status' => $status,
            'message' => $message
        );
        $data_raw = json_encode($data);
        die($data_raw);
    }

?>