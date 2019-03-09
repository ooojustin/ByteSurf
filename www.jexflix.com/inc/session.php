<?php
	
	session_set_cookie_params(86400);
	session_start();

	// establish user data
	if (isset($_SESSION['id']))
		$GLOBALS['user'] = get_user_by_id($_SESSION['id']);

	// handle logout
	if (isset($_GET['logout'])) {
        session_destroy();
        header("location: https://jexflix.com/login/");
        die();
    }

    // function to require a login
	function require_login() {
		if (!is_logged_in()) {
			header("location: https://jexflix.com/login/");
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
			header("location: https://jexflix.com/pricing/");
			die();
		}
	}

	// function to require administrator access (role >= 1000)
	function require_administrator() {
		if (!is_administrator()) {
			header("location: https://jexflix.com/home/");
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

?>