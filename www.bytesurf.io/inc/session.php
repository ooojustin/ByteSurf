<?php
    
    define('ONE_MONTH', 2629746); // one month in seconds
    session_set_cookie_params(ONE_MONTH); // cookie lifetime = 1 month 

	$started = @session_start();
	if(!$started) {
		session_regenerate_id(true); // replace the Session ID
		session_start(); 
	}

	// establish user data
	if (isset($_SESSION['id']))
		$GLOBALS['user'] = get_user_by_id($_SESSION['id']);

	// handle logout
	if (isset($_GET['logout'])) {
        session_destroy();
        header("location: https://bytesurf.io/login/");
        die();
    }

    // function to require a login
	function require_login() {
        $_SESSION['login_redirect'] = $GLOBALS['current_url'];
		if (!is_logged_in()) {
			header("location: https://bytesurf.io/login/?r=1");
        	die();
       	}
	}

	// function to check if a user is logged in
	function is_logged_in() {
		return isset($_SESSION['id']);
	}

	// function to require a subscription
	function require_subscription() {
		require_login(); // must be logged in to check subscription
		global $user;
		$expires = intval($user['expires']);
		// note: expires == -1 means lifetime
		if (time() > $expires && $expires != -1) {
			header("location: https://bytesurf.io/pricing/");
			die();
		}
	}

	// function to require administrator access (role >= 1000)
	function require_administrator() {
		if (!is_administrator()) {
			header("location: https://bytesurf.io/home/");
			die();
		}
	}

	// returns whether or not the logged in user is an administrator (role >= 1000)
	function is_administrator() {
		require_login();
		global $user;
		$role = intval($user['role']);
		return $role >= 1000;
	}

	// function to get subscription expiration
	function get_subscription_expiration_date() {
		require_subscription(); // must have a subscription to determine this
		global $user, $ip_info; // use ip_info to convert timezone
		$expires = intval($user['expires']);
		return ($expires == -1) ? 'Lifetime' : get_time_string($expires);
	}

    // generate and set a request auth token
    // stores raw version in $_SESSION['token'], returns encryption version
    function generate_request_token() {
        $token_raw = generate_split_string(3, 3);
        $token = base64_encode($token_raw);
        $_SESSION['token'] = $token_raw;
        return $token;
    }

    // provide encoded token from client
    // decodes it and compares it to session var
    function verify_request_token($token) {
        
        if (!isset($token) || !isset($_SESSION['token']))
            return false;
        
        // determine decrypted token & what to compare it to
        $token_correct = $_SESSION['token'];
        $token_raw = base64_decode($token);
        
        // unset token, return whether or not it was correct
        unset($_SESSION['token']);
        return $token_correct == $token_raw;
        
    }

?>