<?php
	
	session_start();

	// establish user data
	if (isset($_SESSION['id']))
		$GLOBALS['user'] = get_user_by_id($_SESSION['id']);

	// handle logout
	if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['id']);
        header("location: https://jexflix.com/login/");
        die();
    }

    // function to require a login
	function require_login() {
		if (!isset($_SESSION['id'])) {
			header("location: https://jexflix.com/login/");
        	die();
       	}
	}

	// function to require a subscription
	function require_subscription() {
		require_login(); // must be logged in to check subscription
		global $user;
		if (time() <= intval($user['expires'])) {
			header("location: https://jexflix.com/pricing/");
			die();
		}
	}

?>