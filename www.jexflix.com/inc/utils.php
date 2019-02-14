<?php
	
	// utils.php
	// Functions used generally in other parts of the website.

	function login($email, $password) {
		global $db;
		$check_login = $db->prepare('SELECT * FROM users WHERE email=:email');
		$check_login->bindValue(':email', $email);
		$check_login->execute();
		$user = $check_login->fetch();
		return $user && password_verify($password, $user['password']);
	}

	function create_account($email, $password) {
		global $db;
		$create_account = $db->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
		$create_account->bindValue(':email', $email);
		$create_account->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
		$create_account->execute();
	}

	function get_movie_data($url) {
		global $db;
		$get_data = $db->prepare('SELECT * FROM movies WHERE url=:url');
    	$get_data->bindValue(':url', $url);
    	$get_data->execute();
   		return $get_data->fetch();
	}

	function authenticate_cdn_url($url) {

		// important vars
		$key = '04187e37-4014-48cf-95f4-d6e6ea6c5094';
		$base_url = 'https://cdn.jexflix.com';

		// determine the path of the file (remove base url)
		$path = str_replace('https://cdn.jexflix.com', '', $url);

		// Set the time of expiry to one day from now
		$expires = time() + (60 * 60 * 24); 

		// establish token data
		$hash_me = $securityKey.$path.$expires;

		// to enable ip validation:
		// $hash_me .= "146.14.19.7";

		$token = md5($hash_me, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token);  

		// generate new url
		$url = "{$base_url}{$path}?token={$token}&expires={$expires}";

	}

?>